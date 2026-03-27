<?php
    namespace app\models;

    use app\Enums\RelacaoParentesco\RelacaoParentesco;
    use App\Enums\tipoRendimento\TipoRendimento;

    class rendimento {
        public function __construct(
            private string $descricao,
            private float $valor,
            private TipoRendimento $tipo,

        ) {}

        public function getDescricao(): string { return $this->descricao; }
        public function getValor(): float { return $this->valor; }
        public function getTipo(): TipoRendimento { return $this->tipo; }

    }