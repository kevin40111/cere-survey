<?php

namespace Plat\Survey;

use Plat\Eloquent\Survey as SurveyORM;

class Answer {

    function __construct(SurveyORM\Question $question, SurveyORM\Answer $answer)
    {
        $this->question = $question;
        $this->answer = $answer;
    }

    public static function create(SurveyORM\Question $question, array $attributes)
    {
        $answer = $question->answers()->save(new SurveyORM\Answer([]));

        return new static($question, $answer);
    }

    public static function find($id)
    {
        $answer = SurveyORM\Answer::find($id);

        $question = $answer->question;

        return new static($question, $answer);
    }

    public function update(array $attributes)
    {
        foreach ($attributes as $key => $value)
        {
            $this->answer->$key = $value;
        }

        $this->answer->save();

        return $this;
    }

    public function delete()
    {
        //$this->answer->childrenRule()->delete(); delete childrens

        $deleted = $this->answer->delete();

        return $deleted;
    }

    public function getChildrenModels()
    {
        $rule = $this->answer->childrenRule;

        return $rule ? $rule->questions : \Illuminate\Database\Eloquent\Collection::make([]);
    }

    public function setChildren($question)
    {
        $childrenRule = $this->answer->childrenRule();

        $rule = $childrenRule->getResults() ?: $childrenRule->create([]);

        $rule->questions()->attach([$question->id]);
    }

    public function getModel()
    {
        return $this->answer;
    }

    public function getQuestionModel()
    {
        return $this->question;
    }

}