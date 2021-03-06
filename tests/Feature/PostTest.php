<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostTest extends TestCase
{
    /** @test */
    public function an_authenticated_user_can_create_a_post()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($user = factory(User::class)->create(), 'api');

        $response = $this->post('/api/posts', [
            'body' => 'Testing Body'
        ]);

        $post = Post::first();

        $this->assertCount(1, Post::all());
        $this->assertEquals($user->id, $post->user_id);
        $this->assertEquals('Testing Body', $post->body);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'type' => 'posts',
                    'post_id' => $post->id,
                    'attributes' => [
                        'posted_by' => [
                            'data' => [
                                'attributes' => [
                                    'name' => $user->name,
                                ]
                            ]
                        ],
                        'body' => 'Testing Body'
                    ]
                ],
                'links' => [
                    'self' => url('/posts/' . $post->id)
                ]
            ]);
    }

    /** @test */
    public function an_authenticated_user_can_retrieve_his_own_posts()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($user = factory(User::class)->create(), 'api');

        $posts = factory(Post::class, 2)->create(['user_id' => $user->id]);

        $response = $this->get('/api/posts');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'data' => [
                            'type' => 'posts',
                            'post_id' => $posts->last()->id,
                            'attributes' => [
                                'body' => $posts->last()->body,
                                'posted_at' => $posts->last()->created_at->diffForHumans()
                            ]
                        ]
                    ],
                    [
                        'data' => [
                            'type' => 'posts',
                            'post_id' => $posts->first()->id,
                            'attributes' => [
                                'body' => $posts->first()->body,
                                'posted_at' => $posts->first()->created_at->diffForHumans()
                            ]
                        ]
                    ]
                ],
                'links' => [
                    'self' => url('/posts'),
                ]
            ]);
    }

    /** @test */
    public function an_authenticated_user_can_retrieve_his_own_single_post()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($user = factory(User::class)->create(), 'api');

        $post = factory(Post::class)->create(['user_id' => $user->id]);

        $response = $this->get('/api/posts/' . $post->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'posts',
                    'post_id' => $post->id,
                    'attributes' => [
                        'body' => $post->body,
                        'posted_at' => $post->created_at->diffForHumans()
                    ]
                ],
                'links' => [
                    'self' => url('/posts/' . $post->id),
                ]
            ]);
    }

    /** @test */
    public function an_authenticated_user_can_update_his_own_post()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($user = factory(User::class)->create(), 'api');

        $post = factory(Post::class)->create([
            'user_id' => $user->id,
            'body' => 'testing body'
        ]);

        $response = $this->patch('/api/posts/' . $post->id, [
            'body' => 'updated body'
        ]);

        $post = $post->fresh();

        $this->assertEquals($post->body, 'updated body');
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'posts',
                    'post_id' => $post->id,
                    'attributes' => [
                        'body' => $post->body,
                        'posted_at' => $post->created_at->diffForHumans()
                    ]
                ],
                'links' => [
                    'self' => url('/posts/' . $post->id),
                ]
            ]);
    }

    /** @test */
    public function an_authenticated_user_can_delete_his_own_single_post()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($user = factory(User::class)->create(), 'api');

        $post = factory(Post::class)->create(['user_id' => $user->id]);

        $response = $this->delete('/api/posts/' . $post->id);

        $this->assertCount(0, Post::all());
        $response->assertStatus(200);
    }
}
