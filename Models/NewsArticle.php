<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\News\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\News\Models;

use Modules\Admin\Models\Account;
use Modules\Admin\Models\NullAccount;
use Modules\Comments\Models\CommentList;
use Modules\Tag\Models\Tag;
use phpOMS\Localization\ISO639x1Enum;

/**
 * News article class.
 *
 * @package Modules\News\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class NewsArticle implements \JsonSerializable
{
    /**
     * Article ID.
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

    /**
     * Title.
     *
     * @var string
     * @since 1.0.0
     */
    public string $title = '';

    /**
     * Content.
     *
     * @var string
     * @since 1.0.0
     */
    public string $content = '';

    /**
     * Unparsed.
     *
     * @var string
     * @since 1.0.0
     */
    public string $plain = '';

    /**
     * News type.
     *
     * @var int
     * @since 1.0.0
     */
    public int $type = NewsType::ARTICLE;

    /**
     * News status.
     *
     * @var int
     * @since 1.0.0
     */
    public int $status = NewsStatus::DRAFT;

    /**
     * Language.
     *
     * @var string
     * @since 1.0.0
     */
    public string $language = ISO639x1Enum::_EN;

    /**
     * Unit
     *
     * @var null|int
     * @since 1.0.0
     */
    public ?int $unit = null;

    /**
     * Application
     *
     * @var null|int
     * @since 1.0.0
     */
    public ?int $app = null;

    /**
     * Created.
     *
     * @var \DateTimeImmutable
     * @since 1.0.0
     */
    public \DateTimeImmutable $createdAt;

    /**
     * Creator.
     *
     * @var Account
     * @since 1.0.0
     */
    public Account $createdBy;

    /**
     * Publish.
     *
     * @var \DateTime
     * @since 1.0.0
     */
    public \DateTime $publish;

    /**
     * Featured.
     *
     * @var bool
     * @since 1.0.0
     */
    public bool $isFeatured = false;

    /**
     * Tags.
     *
     * @var Tag[]
     * @since 1.0.0
     */
    public array $tags = [];

    /**
     * Comments
     *
     * @var null|CommentList
     * @since 1.0.0
     */
    public ?CommentList $comments = null;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->createdBy = new NullAccount();
        $this->createdAt = new \DateTimeImmutable('now');
        $this->publish   = new \DateTime('now');
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'plain'      => $this->plain,
            'content'    => $this->content,
            'type'       => $this->type,
            'status'     => $this->status,
            'isFeatured' => $this->isFeatured,
            'publish'    => $this->publish,
            'createdAt'  => $this->createdAt,
            'createdBy'  => $this->createdBy,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }

    use \Modules\Media\Models\MediaListTrait;
}
