<?php

namespace Cere\Survey\Extend;

use Input;
use Cere\Survey\MessageRepository;

trait MessageTrait
{
    public function getMessages()
    {
        $target = $this->hook->applications->find(Input::get('id'));
        return ['messages' => $target->messages()->orderBy('updated_at', 'desc')->get()];
    }

    public function saveMessage()
    {
        $target = $this->hook->applications->find(Input::get('id'));
        return ['message' => MessageRepository::target($target)->saveMessage(Input::get('title'), Input::get('content'))];
    }

    public function updateMessage()
    {
        return ['message' => MessageRepository::updateMessage(Input::get('title'), Input::get('content'), Input::get('message_id'))];
    }

    public function deleteMessage()
    {
        return ['result' => MessageRepository::deleteMessage(Input::get('message_id'))];
    }
}