<?php

namespace Tests\Feature;

use App\Enums\CommentStatus;
use App\Enums\UserRole;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_dapat_mengirim_komentar_dan_menunggu_moderasi(): void
    {
        $article = Article::factory()->create();

        $this->post(route('articles.comment', $article), [
            'name' => 'Budi Tamu',
            'email' => 'budi@example.com',
            'body' => 'Komentar dari tamu.',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'article_id' => $article->id,
            'user_id' => null,
            'author_name' => 'Budi Tamu',
            'author_email' => 'budi@example.com',
            'status' => 'pending',
        ]);
    }

    public function test_komentar_tamu_butuh_nama_dan_email(): void
    {
        $article = Article::factory()->create();

        $this->post(route('articles.comment', $article), ['body' => 'Tanpa identitas'])
            ->assertSessionHasErrors(['name', 'email']);
    }

    public function test_komentar_tamu_tampil_setelah_disetujui(): void
    {
        $article = Article::factory()->create();
        $comment = Comment::factory()->for($article)->create([
            'user_id' => null,
            'author_name' => 'Tamu Setuju',
            'status' => CommentStatus::Pending,
            'body' => 'Isi komentar tamu.',
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertDontSee('Isi komentar tamu.');

        $comment->update(['status' => CommentStatus::Approved]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('Isi komentar tamu.')
            ->assertSee('Tamu Setuju');
    }

    public function test_tamu_dapat_membalas_komentar(): void
    {
        $article = Article::factory()->create();
        $parent = Comment::factory()->for($article)->create();

        $this->post(route('articles.comment', $article), [
            'name' => 'Penjawab',
            'email' => 'balas@example.com',
            'body' => 'Balasan tamu.',
            'parent_id' => $parent->id,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'parent_id' => $parent->id,
            'author_name' => 'Penjawab',
            'status' => 'pending',
        ]);
    }

    public function test_balasan_ke_komentar_berita_lain_ditolak(): void
    {
        $article = Article::factory()->create();
        $foreign = Comment::factory()->create();

        $this->post(route('articles.comment', $article), [
            'name' => 'X',
            'email' => 'x@example.com',
            'body' => 'Salah artikel.',
            'parent_id' => $foreign->id,
        ])->assertSessionHasErrors('parent_id');
    }

    public function test_komentar_staf_langsung_disetujui(): void
    {
        $article = Article::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post(route('articles.comment', $article), [
            'body' => 'Komentar staf.',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'article_id' => $article->id,
            'user_id' => $admin->id,
            'status' => 'approved',
        ]);
    }

    public function test_komentar_ditutup_menolak_kirim(): void
    {
        $article = Article::factory()->create();
        Setting::set('comments_enabled', '0');

        $this->post(route('articles.comment', $article), [
            'name' => 'Tamu',
            'email' => 't@example.com',
            'body' => 'Ditolak.',
        ])->assertForbidden();
    }

    public function test_identitas_tamu_disimpan_di_cookie(): void
    {
        $article = Article::factory()->create();

        $this->post(route('articles.comment', $article), [
            'name' => 'Budi Tamu',
            'email' => 'budi@example.com',
            'body' => 'Komentar dari tamu.',
            'remember' => '1',
        ])->assertCookie('comment_author_name')->assertCookie('comment_author_email');
    }

    public function test_identitas_tamu_tidak_disimpan_tanpa_persetujuan(): void
    {
        $article = Article::factory()->create();

        $this->post(route('articles.comment', $article), [
            'name' => 'Budi Tamu',
            'email' => 'budi@example.com',
            'body' => 'Komentar dari tamu.',
        ])->assertCookieMissing('comment_author_name');
    }

    public function test_identitas_tamu_diprefill_ke_form(): void
    {
        $article = Article::factory()->create();

        $this->withCookie('comment_author_name', 'Budi Prefill')
            ->withCookie('comment_author_email', 'prefill@example.com')
            ->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('value="Budi Prefill"', false)
            ->assertSee('value="prefill@example.com"', false);
    }
}
