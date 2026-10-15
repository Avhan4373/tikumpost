<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CommentStatus;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(Request $request): View
    {
        $comments = Comment::with(['user', 'article'])
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.comments.index', compact('comments'));
    }

    public function update(Request $request, Comment $comment): RedirectResponse
    {
        $status = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
        ])['status'];

        $comment->update(['status' => CommentStatus::from($status)]);

        return back()->with('status', 'Status komentar diperbarui.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('status', 'Komentar dihapus.');
    }
}
