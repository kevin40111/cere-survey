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

        $request = \RequestFile::has('isDoc.isFile')->with(['isDoc.isFile' => function($query) {
            $query->where('type', '31');
        }])->get()->map(function($item) {
            return $item->target_id;
        })->toArray();

        $request = array_values(array_unique($request));

        $users = \Plat\Group::find(Input::get('group_id'))->users->load(['members' => function($query) use ($project_id) {
            $query->where('project_id', $project_id);
        }, 'members.contact', 'members.organizations.now'])->filter(function($user) {
            return $user->actived == true && sizeof($user->members) > 0;
        })->map(function($user) use ($request) {
            $user->hasRequested = in_array($user->id, $request);
            return $user;
        });

        return [
            'users' => $users,
        ];
    }
}
