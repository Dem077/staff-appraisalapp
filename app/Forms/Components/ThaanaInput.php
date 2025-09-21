<?php

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Contracts\CanBeLengthConstrained;
use Filament\Forms\Components\Concerns\CanDisableGrammarly;
use Filament\Support\Concerns\HasExtraAlpineAttributes;

class ThaanaInput extends Field implements CanBeLengthConstrained
{
    use CanDisableGrammarly;
    use \Filament\Forms\Components\Concerns\CanBeAutocompleted;
    use \Filament\Forms\Components\Concerns\CanBeLengthConstrained;
    use \Filament\Forms\Components\Concerns\CanBeReadOnly;
    use \Filament\Forms\Components\Concerns\HasExtraInputAttributes;
    use \Filament\Forms\Components\Concerns\HasPlaceholder;
    use HasExtraAlpineAttributes;

    protected string $view = 'forms.components.thaana-input';

    protected int|Closure|null $cols = null;
    protected int|Closure|null $rows = null;
    protected bool|Closure $shouldAutosize = false;

    public function autosize(bool|Closure $condition = true): static
    {
        $this->shouldAutosize = $condition;
        return $this;
    }

    public function cols(int|Closure|null $cols): static
    {
        $this->cols = $cols;
        return $this;
    }

    public function rows(int|Closure|null $rows): static
    {
        $this->rows = $rows;
        return $this;
    }

    public function getCols(): ?int
    {
        return $this->evaluate($this->cols);
    }

    public function getRows(): ?int
    {
        return $this->evaluate($this->rows);
    }

    public function shouldAutosize(): bool
    {
        return (bool) $this->evaluate($this->shouldAutosize);
    }
}
