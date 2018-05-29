<?php

namespace Cere\Survey\Extend;

use Illuminate\Database\Eloquent\Collection;

class Browser
{
    public static function getQuestions($book)
    {
        return $book->childrenNodes->map(function ($page) {
            $nodes = $page->childrenNodes->load('skipers')->reduce(function ($carry, $node) {
                return $carry->merge(static::getNestNodes($node));
            }, new Collection);
            return ['nodes' => $nodes];
        });
    }

    private static function getNestNodes($node)
    {
        $carry = new Collection([$node]);
        $carry = $node->questions->reduce(function ($carry, $question) {
            $nodes = $question->childrenNodes->load('skipers')->reduce(function ($carry, $node) {
                return $carry->merge(static::getNestNodes($node));
            }, $carry);
            return $carry->merge($nodes);
        }, $carry);

        $carry = $node->answers->reduce(function ($carry, $answer) {
            $nodes = $answer->childrenNodes->load('skipers')->reduce(function ($carry, $node) {
                return $carry->merge(static::getNestNodes($node));
            }, $carry);
            return $carry->merge($nodes);
        }, $carry);

        return $carry;
    }
}
