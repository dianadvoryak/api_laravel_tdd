<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /** @test */
    public function a_post_can_be_stored()
    {
        $file = File::create('my_image.jpg');

        $this->withoutExceptionHandling();
        $data = [
            'title' => 'Some title',
            'description' => 'Some description',
            'image' => $file,
        ];

        $res = $this->post('/posts', $data);

        $res->assertOk();

        $this->assertDatabaseCount('posts', 1);

        $post = Post::first();

        $this->assertEquals($data['title'], $post->title);
        $this->assertEquals($data['description'], $post->description);
        $this->assertEquals('images/' . $file->hashName(), $post->image);

        Storage::disk('local')->assertExists($post->image);
    }

    /** @test */
    public function attribute_title_is_required_for_storing_post()
    {
        $data = [
            'title' => '',
            'description' => 'Some description',
            'image' => '',
        ];

        $res = $this->post('/posts', $data);

        $res->assertRedirect();
        $res->assertInvalid('title');
    }

    /** @test */
    public function attribute_image_is_file_for_storing_post()
    {
        $data = [
            'title' => 'title',
            'description' => 'Some description',
            'image' => 'qweqwe',
        ];

        $res = $this->post('/posts', $data);

        $res->assertRedirect();
        $res->assertInvalid('image');
    }

    /** @test */
    public function a_post_can_be_updated()
    {
        $this->withoutExceptionHandling();

        $post = Post::factory()->create();
        $file = File::create('my_image.jpg');

        $data = [
            'title' => 'Some title edited',
            'description' => 'Some description edited',
            'image' => $file,
        ];

        $res = $this->patch('/posts/' . $post->id, $data);

        $res->assertOk();

        $updatePost = Post::first();

        $this->assertEquals($data['title'], $updatePost->title);
        $this->assertEquals($data['description'], $updatePost->description);
//        $this->assertEquals('images/' . $file->hashName(), $updatePost->image);

        $this->assertEquals($post->id, $updatePost->id);
    }

    /** @test */
    public function response_for_route_posts_index_is_view_post_index_with_posts()
    {
        $this->withoutExceptionHandling();

        $posts = Post::factory(10)->create();

        $res = $this->get('/posts');

        $res->assertViewIs('posts.index');

        $res->assertSeeText('View page');

        $titles = $posts->pluck('title')->toArray();
        $res->assertSeeText($titles);
    }

    /** @test */
    public function response_for_route_show_index_is_view_post_show_with_single_post()
    {
        $this->withoutExceptionHandling();
        $posts = Post::factory()->create();

        $res = $this->get('/posts/' . $posts->id);
        $res->assertViewIs('posts.show');
        $res->assertSeeText('Show page');
        $res->assertSeeText($posts->title);
        $res->assertSeeText($posts->description);
    }
}
