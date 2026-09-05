<?php
namespace App\Http\Controllers\Admin;
use App\Models\Article;
class ArticleController extends ContentController {
    protected string $model = Article::class; protected string $view = 'articles'; protected string $route = 'articles'; protected ?string $uploadField = 'cover_image'; protected ?string $slugField = 'slug';
    protected array $rules = ['title'=>['required','string','max:255'],'excerpt'=>['nullable','string','max:500'],'content'=>['required','string'],'cover_image'=>['nullable','image','max:2048'],'is_published'=>['nullable','boolean'],'published_at'=>['nullable','date']];
}
