<?php

namespace App\Support;

class NavigationGroups
{
    /**
     * @return array<int, string>
     */
    public static function forCurrentUser(): array
    {
        $groups = ['Keuangan'];

        if (AccessControl::isOwner() || AccessControl::isKonterPic()) {
            $groups[] = 'Konter';
        }

        if (AccessControl::isOwner() || AccessControl::isBengkelPic()) {
            $groups[] = 'Bengkel';
        }

        if (AccessControl::isOwner()) {
            $groups[] = 'Administrasi';
        }

        return $groups;
    }
}
