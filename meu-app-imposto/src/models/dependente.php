<?php

    namespace app\models;

    use app\Enums\RelacaoParentesco;
    use app\Enums\RelacaoParentesco\RelacaoParentesco;

    class Dependente {
        public function __construct(

        private string $nome;
        private string $cpf;
        private string $dataNascimento;
        private bool $eUniversitario;

    )
        //create getter to read a infos
        public function getNome(): string {return $this -> nome;}
        public function getParentesco(): RelacaoParentesco {return $this -> Parentesco; }
    }