<?php

namespace Cere\Survey;

use Cere\Survey\Eloquent as SurveyORM;
use Auth;

class MessageRepository
{
    function __construct($target)
    {
        $this->target = $target;
    }

    public static function target($target)
    {
        return new self($target);
    }

    public function saveMessage($title, $content)
    {
        $message = $this->target->messages()->save(new SurveyORM\Message(
            [
                'title' => $title,
                'content' => $content,
                'user_id' => Auth::id()
            ]));
        return $message;
    }

    public static function updateMessage($title, $content, $message_id)
    {
        $message = SurveyORM\Message::find($message_id);

        if ($message->user_id !== Auth::id()) {
            return false;
        }

        $message->title = $title;
        $message->content = $content;
        $message->save();

        return SurveyORM\Message::find($message_id);
    }

    public static function deleteMessage($message_id)
    {
        $message = SurveyORM\Message::find($message_id);

        if($message->user_id == Auth::id()) {
            $message->delete();
            return true;
        }
        return false;
    }
}
