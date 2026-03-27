<?php

namespace App\Models;

use App\Enums\RelacaoParentesco;

class Dependente {
    public function __construct(
        private string $nome,
        private string $cpf,
        private string $dataNascimento,
        private bool $eUniversitario,
        private RelacaoParentesco $parentesco
    ) {}


    public function getNome(): string { return $this->nome; }
    public function getCpf(): string { return $this->cpf; }
    public function getParentesco(): RelacaoParentesco { return $this->parentesco; }
    public function isUniversitario(): bool { return $this->eUniversitario; }
}