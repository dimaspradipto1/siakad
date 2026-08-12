<?php

namespace App\Traits;

use App\Models\Siswa;
use App\Models\OrangTua;

/**
 * Trait ResolvesStudentFromUser
 *
 * Provides a centralized way to resolve the active Siswa model
 * based on the currently authenticated user (role: siswa or orang tua).
 *
 * For role 'orang tua', it respects session('selected_child_id') so that
 * when a parent selects a specific child, all pages show that child's data.
 */
trait ResolvesStudentFromUser
{
    /**
     * Resolve the active Siswa for the authenticated user.
     *
     * - Role 'siswa'     → find by user_id
     * - Role 'orang tua' → use session('selected_child_id') first;
     *                       fall back to first child linked to the parent.
     *
     * @return \App\Models\Siswa|null
     */
    protected function resolveStudentForCurrentUser(): ?Siswa
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        if ($user->roles === 'siswa') {
            return Siswa::where('user_id', $user->id)->first();
        }

        if ($user->roles === 'orang tua') {
            // 1. Try session-selected child first
            $selectedChildId = session('selected_child_id');
            if ($selectedChildId) {
                $siswa = Siswa::find($selectedChildId);
                if ($siswa) {
                    return $siswa;
                }
            }

            // 2. Fall back to first child linked to this parent account
            $orangTua = OrangTua::where('user_id', $user->id)->first();
            if ($orangTua) {
                return Siswa::where('orang_tua_id', $orangTua->id)->first();
            }
        }

        return null;
    }
}
