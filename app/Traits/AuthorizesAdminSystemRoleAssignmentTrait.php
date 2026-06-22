<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait AuthorizesAdminSystemRoleAssignmentTrait
{
    /**
     * システム管理者権限の付与は、システム管理者の操作に限定する。
     */
    protected function abortIfCannotGrantAdminSystemRole($manage_roles): void
    {
        if (!is_array($manage_roles) || !array_key_exists('admin_system', $manage_roles)) {
            return;
        }

        if (!$this->canGrantAdminSystemRole()) {
            abort(403, '権限がありません。');
        }
    }

    /**
     * ログインユーザがシステム管理者権限を付与できるか判定する。
     */
    protected function canGrantAdminSystemRole(): bool
    {
        return Auth::user()->can('admin_system');
    }
}
