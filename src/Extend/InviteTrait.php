<?php

namespace Cere\Survey\Extend;

use Input;
use View;
use Plat\Group;

trait InviteTrait
{
    public function invites()
    {
        return 'survey::extend.invite.invites';
    }

    public function getGroups()
    {
        return ['groups' => $this->user->groups];
    }

    public function getMembers()
    {
        $project = $this->user->members()->orderBy('logined_at', 'desc')->first()->project;

        $applications = $this->hook->applications->keyBy('member_id');

        $groupUsers = Group::find(Input::get('group_id'))->users->lists('id');
        $members = $project->members->load('user')->filter(function ($member) use ($groupUsers) {
            return in_array($member->user->id, $groupUsers) && $member->user->actived;
        })->load('contact', 'organizations.now')->each(function ($member) use ($applications) {
            $member->application = isset($applications[$member->id]) ? $applications[$member->id] : null;
        })->values();

        return ['members' => $members];
    }
}
