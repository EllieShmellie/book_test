<?php

namespace app\components;

use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use Yii;
use yii\web\HttpException;

class SmsPilot extends Component
{
    public string $apiKey;
    public string $apiUrl = 'https://smspilot.ru/api2.php';

    public function init(): void
    {
        parent::init();
        if (empty($this->apiKey)) {
            throw new InvalidConfigException('SmsPilot: ApiKey должен быть заполнен.');
        }
    }

    /**
     * @param mixed $phones
     * @param string $message
     * @param array $additionalParams
     * @return array|null
     */
    public function send($phones, string $message, array $additionalParams = []): ?array
    {
        $messages = $this->prepareMessages($phones, $message);
        return $this->sendBatch($messages, $additionalParams);
    }

    /**
     * @param array $messages
     * @param array $additionalParams
     * @return array|null
     */
    public function sendBatch(array $messages, array $additionalParams = []): ?array
    {
        if (empty($messages)) {
            Yii::warning('SmsPilot: Пустой массив', __METHOD__);
            return null;
        }

        $postData = $this->buildPostData($messages, $additionalParams);
        return $this->makeRequest($postData);
    }

    public function calculateCost($phones, string $message, array $additionalParams = []): ?float
    {
        $messages = $this->prepareMessages($phones, $message);
        
        $postData = $this->buildPostData($messages, $additionalParams);
        $postData['cost'] = 1;

        $response = $this->makeRequest($postData);
        return $response['cost'] ?? null;
    }

    /**
     * @param mixed $phones
     * @param string $message
     * @return array{text: string, to: mixed[]}
     */
    private function prepareMessages($phones, string $message): array
    {
        $phones = is_array($phones) ? $phones : [$phones];
        
        return array_map(fn($phone) => [
            'to' => $phone,
            'text' => $message,
        ], $phones);
    }

    /**
     * @param array $sendData
     * @param array $additionalParams
     * @return array{apikey: string, send: array}
     */
    protected function buildPostData(array $sendData, array $additionalParams = []): array
    {
        $allowedParams = ['from', 'ttl', 'callback', 'callback_method', 'test'];
        $filteredParams = array_intersect_key($additionalParams, array_flip($allowedParams));

        return array_merge([
            'apikey' => $this->apiKey,
            'send' => $sendData,
        ], $filteredParams);
    }

    /**
     * @param array $postData
     * @throws \yii\web\HttpException
     */
    protected function makeRequest(array $postData): ?array
    {
        try {
            $jsonBody = Json::encode($postData);
        } catch (\InvalidArgumentException $e) {
            throw new HttpException(500, 'JSON encode: ' . $e->getMessage());
        }

        $ch = curl_init($this->apiUrl);
        
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonBody,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json; charset=utf-8',
                'Accept: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            throw new HttpException(500, "CURL error: $error");
        }

        if ($httpCode !== 200) {
            throw new HttpException($httpCode, "SmsPilot API вернул HTTP $httpCode");
        }

        try {
            $result = Json::decode($response);
        } catch (\InvalidArgumentException $e) {
            throw new HttpException(500, 'Неверный JSON: ' . $e->getMessage());
        }

        if (isset($result['error'])) {
            $error = $result['error'];
            throw new HttpException(500, "API Error {$error['code']}: {$error['description']}");
        }
        return $result;
    }
}