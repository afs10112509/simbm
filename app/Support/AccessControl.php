<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AccessControl
{
    public const ROLE_OWNER = 'owner';

    public const ROLE_PIC = 'pic';

    public const BRANCH_KONTER = 'konter';

    public const BRANCH_BENGKEL = 'bengkel';

    public static function user(): User
    {
        return auth()->user();
    }

    public static function isOwner(): bool
    {
        return self::user()->hasRole(self::ROLE_OWNER);
    }

    public static function isPic(): bool
    {
        return self::user()->hasRole(self::ROLE_PIC);
    }

    public static function userBranch(): ?Branch
    {
        return self::user()->branch;
    }

    public static function userBranchId(): ?int
    {
        $branchId = self::user()->branch_id;

        return $branchId !== null ? (int) $branchId : null;
    }

    public static function picHasBranch(): bool
    {
        return self::isPic() && self::userBranchId() !== null;
    }

    public static function isKonterBranch(?Branch $branch = null): bool
    {
        $branch ??= self::userBranch();

        return $branch?->type === self::BRANCH_KONTER;
    }

    public static function isBengkelBranch(?Branch $branch = null): bool
    {
        $branch ??= self::userBranch();

        return $branch?->type === self::BRANCH_BENGKEL;
    }

    public static function isKonterPic(): bool
    {
        return self::picHasBranch() && self::isKonterBranch();
    }

    public static function isBengkelPic(): bool
    {
        return self::picHasBranch() && self::isBengkelBranch();
    }

    public static function canViewAllBranches(): bool
    {
        return self::isOwner();
    }

    public static function canManageUsers(): bool
    {
        return self::isOwner();
    }

    public static function canManageMasterData(): bool
    {
        return self::isOwner();
    }

    public static function canManageEmployees(): bool
    {
        return self::isOwner();
    }

    public static function canInputFinancialTracker(): bool
    {
        return self::picHasBranch();
    }

    public static function canViewTransactions(): bool
    {
        return self::user()->hasAnyRole([
            self::ROLE_OWNER,
            self::ROLE_PIC,
        ]);
    }

    public static function canCreateTransaction(): bool
    {
        return false;
    }

    public static function canEditTransaction(?object $record = null): bool
    {
        if (! self::picHasBranch()) {
            return false;
        }

        if ($record instanceof Transaction && $record->isLinkedToServiceRecord()) {
            return false;
        }

        return $record === null || self::userOwnsBranchRecord($record);
    }

    public static function canDeleteTransaction(?object $record = null): bool
    {
        return self::canEditTransaction($record);
    }

    public static function canManageTransfers(): bool
    {
        return self::picHasBranch();
    }

    public static function canViewFinancialReport(): bool
    {
        return self::user()->hasAnyRole([
            self::ROLE_OWNER,
            self::ROLE_PIC,
        ]);
    }

    public static function canAccessBrilink(): bool
    {
        return self::isOwner() || self::isKonterPic();
    }

    public static function canManageBrilink(): bool
    {
        return self::isKonterPic();
    }

    public static function canAccessService(): bool
    {
        return self::isOwner() || self::isKonterPic();
    }

    public static function canManageService(): bool
    {
        return self::isKonterPic();
    }

    public static function canManageServiceTechnicians(): bool
    {
        return self::isKonterPic();
    }

    public static function canViewServiceTechnicians(): bool
    {
        return self::canAccessService();
    }

    public static function canViewServiceReport(): bool
    {
        return self::canAccessService();
    }

    public static function canAccessUpahKerja(): bool
    {
        return self::isOwner() || self::isBengkelPic();
    }

    public static function canManageUpahKerja(): bool
    {
        return self::isBengkelPic();
    }

    public static function canManageWorkers(): bool
    {
        return self::isBengkelPic();
    }

    public static function canViewWorkers(): bool
    {
        return self::canAccessUpahKerja();
    }

    public static function canViewUpahKerjaReport(): bool
    {
        return self::canAccessUpahKerja();
    }

    public static function canViewDashboardWidgets(): bool
    {
        return self::user()->hasAnyRole([
            self::ROLE_OWNER,
            self::ROLE_PIC,
        ]);
    }

    public static function canViewOwnerDashboardWidgets(): bool
    {
        return self::isOwner();
    }

    public static function roleRequiresBranch(string $role): bool
    {
        return $role !== self::ROLE_OWNER;
    }

    public static function validateRoleForBranch(string $role, ?Branch $branch): bool
    {
        if ($role === self::ROLE_OWNER) {
            return true;
        }

        if ($role === self::ROLE_PIC) {
            return $branch !== null;
        }

        return false;
    }

    public static function userHasValidBranchForRole(User $user, string $role): bool
    {
        if ($role === self::ROLE_OWNER) {
            return true;
        }

        return self::validateRoleForBranch($role, $user->branch);
    }

    public static function scopeToUserBranch(Builder $query, string $column = 'branch_id'): Builder
    {
        if (self::canViewAllBranches()) {
            return $query;
        }

        return $query->where($column, self::user()->branch_id);
    }

    public static function scopeTransactionsForUser(Builder $query): Builder
    {
        return self::scopeToUserBranch($query);
    }

    public static function userOwnsBranchRecord(object $record): bool
    {
        if (self::canViewAllBranches()) {
            return true;
        }

        return (int) $record->branch_id === (int) self::user()->branch_id;
    }

    public static function roleLabels(): array
    {
        return [
            self::ROLE_OWNER => 'Pemilik',
            self::ROLE_PIC => 'PIC / Kepala Toko',
        ];
    }

    public static function branchTypeLabels(): array
    {
        return [
            self::BRANCH_KONTER => 'Konter',
            self::BRANCH_BENGKEL => 'Bengkel',
        ];
    }
}
