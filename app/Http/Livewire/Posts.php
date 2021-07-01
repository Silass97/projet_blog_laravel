<?php

namespace App\Http\Livewire;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;


class Posts extends Component
{   
    use WithFileUploads;
    use WithPagination;
    public $title;
    public $body;
    public $image;
    public $postId = null;
    public $newImage;

    public $showModalForm = false;

    public function showCreatPostModal(){
        $this->showModalForm = true;
    }
    public function storePost(){
        $this->validate([
        'title' => 'required',
        'body' => 'required',
        'image' => 'required|image|max:1024',
        ]);

        $image_name = $this->image->getClientOriginalName();
        $this->image->storeAs('public/photos/', $image_name);
        $post = new Post();
        $post->user_id = auth()->user()->id;
        $post->title = $this->title;
        $post->slug = str::slug($this->title);
        $post->body = $this->body;
        $post->image = $image_name;
        $post->save();
        $this->reset();
        session()->flash('message', 'Post Created Successfully');

    }

    public function showEditPostModal($id)
    {   
        $this->reset();
        $this->showModalForm = true;
        $this->postId = $id;
        $this->loadEditForm();
    }

    public function loadEditForm()
    {
        $post = Post::findOrFail($this->postId);
        $this->title = $post->title;
        $this->body = $post->body;
        $this->newImage = $post->image;

    }

    public function updatePost()
    {
        $this->validate([
            'title' => 'required',
            'body' => 'required',
            'image' => 'image|max:1024|nullable'
            ]);
        if ($this->image) {
            Storage::delete('public/photos/', $this->newImage);
            $this->newImage = $this->image->getClientOriginalName();
            $this->image->storeAs('public/photos/', $this->newImage);
           
        }

        Post::find($this->postId)->update([

            'title' => $this->title,
            'body'  => $this->body,
            'image' => $this->newImage
        ]);
        $this->reset();
        session()->flash('message', 'Post Updated Successfully');
    }

    public function deletePost($id)
    {
        $post = Post::find($id);
        Storage::delete('public/photos/', $post->image);
        $post->delete();
        session()->flash('message', 'Post Deleted Successfully');
    }

    public function render()
    {
        return view('livewire.posts',[
            'posts' => Post::orderBy('created_at', 'DESC')->paginate(5)
        ]);
    }
}
