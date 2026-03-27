<?php

    namespace app\models;

    use app\Enums\RelacaoParentesco;
    use app\Enums\RelacaoParentesco\RelacaoParentesco;

    class Dependente {
        public string $nome;
        public string $cpf;
        public string $dataNascimento;
        public bool $eUniversitario;

        // Chamar enum
        public RelacaoParentesco $relacaoParentesco;
    }