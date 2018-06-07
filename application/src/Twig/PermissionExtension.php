<?php

namespace App\Twig;

use App\Entity\Project;
use App\Service\PermissionManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PermissionExtension extends AbstractExtension
{
    protected $permissionManager;

    public function __construct(PermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('isAuthorizedOnProject', array($this, 'isAuthorizedOnProject')),
        );
    }

    public function isAuthorizedOnProject(Project $project, $action)
    {
        return $this->permissionManager->isAuthorizedOnProject($project, $action);
    }
}
