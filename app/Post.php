<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use Sluggable;

    const IS_DRAFT = 0;
    const IS_PUBLIC = 1;
    const IS_FEATURED = 1;
    const IS_STANDARD = 0;

    protected $fillable = [
        'titile',
        'content'
    ];

    public function category()
    {
        return $this->hasOne(Category::class);
    }

    public function author()
    {
        return $this->hasOne(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'post_tags',
            'post_id',
            'tag_id'
        );
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public static function add($fields)
    {
        $post = new static();
        $post->fill($fields);
        $post->user_id = 1;
        $post->save();

        return $post;
    }

    public function edit($fields)
    {
        $this->fill($fields);
        $this->save();
    }

    public function remove()
    {
        // Перед удалением поста удалим все ненужное (картинки и т.д.)
        Storage::delete('uploads/' . $this->image);
        $this->delete();
    }

    public function uploadImage($image)
    {
        if ($image == null) return;

        Storage::delete('uploads/' . $this->image);
        $filename = str_random(10) . '.' . $image->extension();
        $image->saveAs('uploads', $filename);
        $this->image = $filename;
        $this->save();
    }

    public function getImage()
    {
        if ($this->image == null) {
            return '/img/no-image.png';
        }

        return '/uploads/' . $this->image;
    }

    public function setCategory($id)
    {
        if ($id == null) return;

        $this->category_id = $id;
        $this->save();
    }

    public function setTags($ids)
    {
        if ($ids == null) return;

        $this->tags()->sync($ids);
    }

    public function setDraft()
    {
        $this->status = Post::IS_DRAFT;
        $this->save();
    }

    public function setPublic()
    {
        $this->status = Post::IS_PUBLIC;
        $this->save();
    }

    public function toggleStatus($value)
    {
        if ($value == null) {
            return $this->setDraft();
        } else {
            return $this->setPublic();
        }
    }

    public function setFeatured()
    {
        $this->is_featured = Post::IS_FEATURED;
        $this->save();
    }

    public function setStandard()
    {
        $this->is_featured = Post::IS_STANDARD;
        $this->save();
    }

    public function toggleFeatured($value)
    {
        if ($value == null) {
            return $this->setStandard();
        } else {
            return $this->setFeatured();
        }
    }
}

