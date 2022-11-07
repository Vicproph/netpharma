<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\PostSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;


class PostTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private const BASE_URL = 'api/posts/';
    private User $authenticatedUser;

    public function setUp(): void
    {
        parent::setUp();

        (new PostSeeder)->run();
        $this->authenticatedUser = Post::first()->user;
        $this->withHeader('Accept', 'application/json');

        // $this->withoutExceptionHandling();
    }

    public function test_index()
    {
        $response = $this
            ->actingAs($this->authenticatedUser, 'api')
            ->get(self::BASE_URL)
            ->assertOk();
        $json = $response->json();
        $posts = $json['data'];

        foreach ($posts as $post) {
            $this->assertResponseAssertions($post);
        }

        $perPage = $json['meta']['per_page'];
        $this->assertEquals(20, $perPage);
    }

    public function test_show_action_will_show_users_own_post()
    { // user should see this post

        $postId = 1;
        $response = $this
            ->actingAs($this->authenticatedUser, 'api')
            ->get(self::BASE_URL . $postId)
            ->assertOk();
        $json = $response->json();
        $post = $json['data'];
        $this->assertResponseAssertions($post);
    }

    public function test_show_action_will_not_show_nonexisting_posts()
    { // user should see this post

        $postId = 1000000;
        $response = $this
            ->actingAs($this->authenticatedUser, 'api')
            ->get(self::BASE_URL . $postId)
            ->assertNotFound();

        $response->assertExactJson(['message' => __('resource.notFound')]);
    }

    public function test_show_action_will_not_show_other_users_posts()
    {

        $postId = Post::where('user_id', '<>', $this->authenticatedUser->id)->first()->id;

        $response = $this
            ->actingAs($this->authenticatedUser, 'api')
            ->get(self::BASE_URL . $postId)
            ->assertForbidden();

        $message = $response->json()['message'];

        $this->assertEquals(__('resource.actionUnauthorized'), $message);
    }

    public function test_user_can_delete_their_own_post()
    {

        $postId = 1;

        $response = $this
            ->actingAs($this->authenticatedUser, 'api')
            ->delete(self::BASE_URL . $postId)
            ->assertOk();

        $response->assertExactJson(['message' => __('resource.deleted')]);
    }

    public function test_user_cannot_delete_nonexisting_posts()
    {

        $postId = 100000;
        $response = $this
            ->actingAs($this->authenticatedUser, 'api')
            ->delete(self::BASE_URL . $postId)
            ->assertNotFound();

        $response->assertExactJson(['message' => __('resource.notFound')]);
    }

    public function test_user_cannot_delete_others_posts()
    {

        $postId = Post::where('user_id', '<>', $this->authenticatedUser->id)->first()->id;

        $response = $this
            ->actingAs($this->authenticatedUser, 'api')
            ->delete(self::BASE_URL . $postId)
            ->assertForbidden();

        $response->assertExactJson(['message' => __('resource.actionUnauthorized')]);
    }

    public function test_user_cannot_create_post_unauthenticated()
    {
        $attributes = $this->generatePost(true, true);

        $this
            ->post(self::BASE_URL, $attributes)
            ->assertUnauthorized();
    }

    public function test_user_can_create_post()
    {

        $attributes = $this->generatePost(true, true);
        $response = $this
            ->actingAs($this->authenticatedUser, 'api')
            ->post(self::BASE_URL, $attributes)
            ->assertCreated();
        $post = $response->json()['data'];

        $this->assertResponseAssertions($post);
        $this->assertEquals(
            $attributes['title'],
            $post['title']
        );
        $this->assertEquals(
            $attributes['body'],
            $post['body']
        );
        $this->assertEquals(
            $this->authenticatedUser->id,
            $post['user']['id']
        );
    }


    public function test_user_cant_create_post_with_bad_title()
    {

        $attributes = $this->generatePost(false, true);
        $this
            ->actingAs($this->authenticatedUser, 'api')
            ->post(self::BASE_URL, $attributes)
            ->assertUnprocessable();
    }

    public function test_user_cant_create_post_with_bad_body()
    {

        $attributes = $this->generatePost(true, false);
        $this
            ->actingAs($this->authenticatedUser, 'api')
            ->post(self::BASE_URL, $attributes)
            ->assertUnprocessable();
    }

    public function test_user_cant_update_post_unauthenticated()
    {
        $postId = Post::first()->id;
        $attributes = $this->generatePost(true, true);

        $this->put(self::BASE_URL . $postId, $attributes)->assertUnauthorized();
    }

    public function test_user_cant_update_nonexisting_post()
    {
        $postId = 100000;
        $attributes = $this->generatePost(true, true);

        $this
            ->actingAs($this->authenticatedUser, 'api')
            ->put(self::BASE_URL . $postId, $attributes)
            ->assertNotFound();
    }

    public function test_user_can_update_post()
    {
        $postId = 1;
        $attributes = $this->generatePost(true, true);

        $response = $this
            ->actingAs($this->authenticatedUser, 'api')->put(self::BASE_URL . $postId, $attributes)
            ->assertOk();
        $json = $response->json();
        $updatedPost = $json['data'];

        $this->assertResponseAssertions($updatedPost);
        $this->assertEquals($postId, $updatedPost['id']);
        $this->assertEquals($attributes['title'], $updatedPost['title']);
        $this->assertEquals($attributes['body'], $updatedPost['body']);
    }

    public function test_user_cant_update_someone_elses_post()
    {
        $postId = Post::where('user_id', '<>', $this->authenticatedUser->id)->first()->id;

        $attributes = $this->generatePost(true, true);

        $r = $this
            ->actingAs($this->authenticatedUser, 'api')->put(self::BASE_URL . $postId, $attributes)
            ->assertForbidden();
    }

    public function test_user_cant_update_post_with_bad_title()
    {
        $postId = 1;

        $attributes = $this->generatePost(false, true);

        $this
            ->actingAs($this->authenticatedUser, 'api')->put(self::BASE_URL . $postId, $attributes)
            ->assertUnprocessable();
    }

    public function test_user_cant_update_post_with_bad_body()
    {
        $postId = 1;

        $attributes = $this->generatePost(true, false);

        $this
            ->actingAs($this->authenticatedUser, 'api')->put(self::BASE_URL . $postId, $attributes)
            ->assertUnprocessable();
    }



    private function generatePost(bool $validTitle, bool $validBody): array
    {
        if ($validTitle)
            $title = Str::random(rand(3, 100));
        else
            $title = Str::random(rand(0, 2));

        if ($validBody)
            $body = Str::random(rand(100, 200));
        else
            $body = Str::random(rand(0, 99));

        return [
            'title' => $title,
            'body' => $body,
        ];
    }

    private function assertResponseAssertions(array $post)
    {
        $this->assertArrayHasKey('id', $post);
        $this->assertArrayHasKey('title', $post);
        $this->assertArrayHasKey('body', $post);
        $this->assertArrayHasKey('user', $post);

        $this->assertNotNull($post['title']);
        $this->assertNotNull($post['body']);
        $this->assertNotNull($post['user']);
    }
}
