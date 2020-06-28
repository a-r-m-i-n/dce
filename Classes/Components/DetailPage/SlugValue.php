<?php declare(strict_types=1);
namespace T3\Dce\Components\DetailPage;

class SlugValue
{
    protected $slug;

    protected $unique;

    public function __construct(string $slug, bool $unique)
    {
        $this->slug = $slug;
        $this->unique = $unique;
    }

    public function wasUnique(): bool
    {
        return $this->unique;
    }

    public function __toString(): string
    {
        return $this->slug;
    }
}
