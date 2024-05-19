<h3>View page</h3>
<div>
    @foreach($posts as $post)
    <div>
        {{ $post->title }}
    </div>
    @endforeach
</div>
