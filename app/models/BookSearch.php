<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class BookSearch extends Book
{
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['book_id', 'year'], 'integer'],
            [['title', 'description', 'isbn', 'cover', 'created_at', 'updated_at'], 'safe'],
            [['author_ids'], 'safe'],
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
     * @return ActiveDataProvider
     */
    public function search($params): ActiveDataProvider
    {
        $query = Book::find()->alias('b')->distinct();
        
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
            'b.book_id'   => $this->book_id,
            'b.year'      => $this->year,
        ]);

        $query->andFilterWhere(['like', 'b.title', $this->title])
              ->andFilterWhere(['like', 'b.description', $this->description])
              ->andFilterWhere(['like', 'b.isbn', $this->isbn])
              ->andFilterWhere(['like', 'b.cover', $this->cover])
              ->andFilterWhere(['like', 'b.created_at', $this->created_at])
              ->andFilterWhere(['like', 'b.updated_at', $this->updated_at]);

        if (!empty($this->author_ids)) {
            $query->andFilterWhere(['ab.author_id' => $this->author_ids]);
        }

        return $dataProvider;
    }
}
