<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class AuthorSearch extends Author
{
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['author_id'], 'integer'],
            [['last_name', 'first_name', 'patronymic', 'created_at', 'updated_at'], 'safe'],
            [['book_ids'], 'safe'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function scenarios(): array
    {
        return Model::scenarios();
    }
    
    /**
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params): ActiveDataProvider
    {
        $query = Author::find()->distinct()->with('books');

        $query->joinWith(['authorBooks' => function ($q) {
            $q->alias('ab');
        }]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'author_id' => $this->author_id,
        ]);

        $query->andFilterWhere(['like', 'last_name', $this->last_name])
              ->andFilterWhere(['like', 'first_name', $this->first_name])
              ->andFilterWhere(['like', 'patronymic', $this->patronymic])
              ->andFilterWhere(['like', 'created_at', $this->created_at])
              ->andFilterWhere(['like', 'updated_at', $this->updated_at]);
        
        if (!empty($this->book_ids)) {
            $query->andFilterWhere(['ab.book_id' => $this->book_ids]);
        }

        return $dataProvider;
    }
}
