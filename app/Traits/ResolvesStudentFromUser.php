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
     * - Role 'orang tua' → respects query/request param, session('selected_child_id'),
     *                       or falls back to first child linked to the parent.
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
            $children = $this->getChildrenForParent($user);

            if ($children->isEmpty()) {
                return null;
            }

            // 1. Check if specific child was requested via request parameter (siswa_id or child_id)
            $reqChildId = request()->get('child_id') ?: request()->get('siswa_id');
            if ($reqChildId && is_numeric($reqChildId)) {
                $matchedChild = $children->firstWhere('id', (int)$reqChildId);
                if ($matchedChild) {
                    session(['selected_child_id' => $matchedChild->id]);
                    return $matchedChild;
                }
            }

            // 2. Try session-selected child
            $selectedChildId = session('selected_child_id');
            if ($selectedChildId) {
                $matchedChild = $children->firstWhere('id', (int)$selectedChildId);
                if ($matchedChild) {
                    return $matchedChild;
                }
            }

            // 3. Fall back to first child
            $firstChild = $children->first();
            session(['selected_child_id' => $firstChild->id]);
            return $firstChild;
        }

        return null;
    }

    /**
     * Get all children for the current parent user.
     *
     * @param \App\Models\User|null $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChildrenForParent($user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user || $user->roles !== 'orang tua') {
            return collect();
        }

        $orangTuaIds = OrangTua::where('user_id', $user->id)->pluck('id')->toArray();
        if ($user->email) {
            $extraIds = OrangTua::where('email', $user->email)->pluck('id')->toArray();
            $orangTuaIds = array_unique(array_merge($orangTuaIds, $extraIds));
        }
        if ($user->name) {
            $nameBase = trim(explode(',', $user->name)[0]);
            $extraIds = OrangTua::where('nama_ayah', 'like', '%' . $nameBase . '%')
                ->orWhere('nama_ibu', 'like', '%' . $nameBase . '%')
                ->pluck('id')->toArray();
            $orangTuaIds = array_unique(array_merge($orangTuaIds, $extraIds));
        }

        if (empty($orangTuaIds)) {
            return collect();
        }

        return Siswa::with(['kelas', 'pembagianKelas.kelas', 'pembagianKelas.tahunAjaran'])
            ->whereIn('orang_tua_id', $orangTuaIds)
            ->get();
    }
}
