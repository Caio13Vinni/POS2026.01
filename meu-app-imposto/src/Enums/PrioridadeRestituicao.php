<?php
    namespace App\Enums\PrioridadeRestituicao;

enum PrioridadeRestituicao: int{
    case IDOSO_MAIS_80 = 1;
    case IDOSO_MAIS_60 = 2;
    case DEFICIENTE_DOENTE = 3;
    case PROFESSOR = 4;
    case COMUM = 5;

    }