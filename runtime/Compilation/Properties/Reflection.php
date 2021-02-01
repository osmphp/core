<?php

declare(strict_types=1);

namespace Osm\Runtime\Compilation\Properties;

use Osm\Runtime\Compilation\Compiler;
use Osm\Runtime\Compilation\Property;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Var_ as PhpDocVar;

/**
 * @property \ReflectionProperty $reflection #[Expected]
 */
class Reflection extends Property
{
    /** @noinspection PhpUnused */
    protected function get_type(): ?string {
        $this->parse();
        return $this->type;
    }

    /** @noinspection PhpUnused */
    protected function get_array(): bool {
        $this->parse();
        return $this->array;
    }

    /** @noinspection PhpUnused */
    protected function get_nullable(): bool {
        $this->parse();
        return $this->nullable;
    }

    /** @noinspection PhpUnused */
    protected function get_attributes(): array {
        global $osm_app; /* @var Compiler $osm_app */

        $attributes = [];

        foreach ($this->reflection->getAttributes() as $attribute) {
            $osm_app->app->addAttribute($attributes, $attribute->getName(),
                $attribute->newInstance());
        }

        return $attributes;
    }

    protected function getDocCommentType(): string {
        if (!($comment = $this->reflection->getDocComment())) {
            return '';
        }

        $factory = DocBlockFactory::createInstance();
        $docBlock = $factory->create($comment, $this->class->type_context);

        if (empty($vars = $docBlock->getTagsByName('var'))) {
            return '';
        }

        /* @var PhpDocVar $var */
        return (string)$vars[0]->getType();
    }

    protected function parse(): void {
        $this->type = null;
        $this->array = false;
        $this->nullable = null;

        $this->parsePhpDocType($this->getDocCommentType());

        $reflection = $this->reflection->getType();

        if ($reflection instanceof \ReflectionUnionType) {
            $reflection = $reflection->getTypes()[0];
        }

        if ($this->type === null) {
            if (($this->type = $reflection->getName()) == 'array') {
                $this->type = null;
                $this->array = true;
            }
        }

        if ($this->nullable === null) {
            $this->nullable = $reflection->allowsNull();
        }
    }
}