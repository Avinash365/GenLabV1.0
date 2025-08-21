<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class PermissionsMatrix extends Component
{
    public $permissions;
    public $oldPermissions;

    public function __construct($permissions, $oldPermissions = [])
    {
        $this->permissions = $permissions;
        $this->oldPermissions = collect($oldPermissions);
    }

    public function render(): View
    {
        return view('components.permissions-matrix');
    }
}
