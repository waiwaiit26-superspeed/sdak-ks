<?php
namespace App\Models;

use App\Core\Model;

/**
 * NewsModel — manages `news` table
 */
class NewsModel extends Model
{
    protected string $table = 'news';

    private array $listCols = [
        'news.id', 'news.title', 'news.slug', 'news.excerpt',
        'news.cover_image', 'news.status', 'news.views',
        'news.published_at', 'news.created_at',
        'users.full_name(author_name)'
    ];

    private array $detailCols = [
        'news.id', 'news.title', 'news.slug', 'news.excerpt',
        'news.content', 'news.cover_image', 'news.status',
        'news.views', 'news.published_at', 'news.created_at', 'news.updated_at',
        'users.full_name(author_name)', 'users.profile_image(author_image)'
    ];

    private array $join = [
        '[>]users' => ['author_id' => 'id']
    ];

    public function getList(array $filters, int $page, int $perPage, bool $isAdmin = false): array
    {
        $where = [];

        if (!$isAdmin) {
            $where['news.status'] = 'published';
        } elseif (!empty($filters['status'])) {
            $where['news.status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where['OR'] = [
                'news.title[~]'   => $s,
                'news.excerpt[~]' => $s,
            ];
        }

        $where['ORDER'] = ['news.published_at' => 'DESC'];

        $countWhere = $where;
        unset($countWhere['ORDER']);
        $total = $this->countJoin($this->join, '*', $countWhere);

        $where['LIMIT'] = [($page - 1) * $perPage, $perPage];
        $data = $this->selectJoin($this->join, $this->listCols, $where);

        return compact('data', 'total', 'page', 'perPage');
    }

    public function getDetail(int $id): ?array
    {
        return $this->getJoin($this->join, $this->detailCols, ['news.id' => $id]);
    }

    public function incrementViews(int $id): void
    {
        $this->update(['views[+]' => 1], ['id' => $id]);
    }

    public static function makeSlug(string $title): string
    {
        $slug = preg_replace('/[^a-zA-Z0-9\p{Thai}]+/u', '-', $title);
        return trim($slug, '-');
    }
}
