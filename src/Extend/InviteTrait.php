<?php

namespace Cere\Survey\Extend;

use Input;
use View;

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

    public function getUsers()
    {
        $project_id = $this->user->members()->orderBy('logined_at', 'desc')->first()->project_id;

        $applications = $this->hook->applications->map(function($application){
            return $application->member_id;
        })->toArray();

        $users = \Plat\Group::find(Input::get('group_id'))->users->load(['members' => function($query) use ($project_id) {
            $query->where('project_id', $project_id);
        }, 'members.contact', 'members.organizations.now'])->filter(function($user) {
            return $user->actived == true && sizeof($user->members) > 0;
        })->map(function($user) use ($applications) {
            $user->application = $this->hook->applications()->where('member_id', $user->members[0]->id)->first();
            $user->hasRequested = in_array($user->members[0]->id, $applications);
            return $user;
        });

        return [
            'users' => $users,
        ];
    }
}
