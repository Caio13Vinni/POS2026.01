<?php

// ==========================================
// 1. CAMADA DE DOMÍNIO (Regras de Negócio)
// ==========================================

interface RegraIrpf {
    public function verificar(float $valor): bool;
    public function getMotivo(float $valor): string;
}

class RegraRenda implements RegraIrpf {
    private const LIMITE = 35584.00;
    public function verificar(float $valor): bool { return $valor > self::LIMITE; }
    public function getMotivo(float $valor): string {
        return "Renda Tributável (R$ " . number_format($valor, 2, ',', '.') . ") excedeu limite de R$ 35.584,00.";
    }
}

class RegraIsentos implements RegraIrpf {
    private const LIMITE = 200000.00;
    public function verificar(float $valor): bool { return $valor > self::LIMITE; }
    public function getMotivo(float $valor): string {
        return "Rendimentos Isentos (R$ " . number_format($valor, 2, ',', '.') . ") excederam limite de R$ 200.000,00.";
    }
}

class RegraBens implements RegraIrpf {
    private const LIMITE = 800000.00;
    public function verificar(float $valor): bool { return $valor > self::LIMITE; }
    public function getMotivo(float $valor): string {
        return "Patrimônio (R$ " . number_format($valor, 2, ',', '.') . ") excedeu limite de R$ 800.000,00.";
    }
}

class RegraBets implements RegraIrpf {
    private const LIMITE = 28467.20;
    public function verificar(float $valor): bool { return $valor > self::LIMITE; }
    public function getMotivo(float $valor): string {
        return "Ganhos com Bets (R$ " . number_format($valor, 2, ',', '.') . ") excederam limite de R$ 28.467,20.";
    }
}

// ==========================================
// 2. CAMADA DE SERVIÇO (Relatório e Persistência)
// ==========================================

class RelatorioIrpf {
    private array $dados = [];

    public function registrarEntrada(string $categoria, float $valor): void {
        $this->dados['entradas'][$categoria] = $valor;
    }

    public function finalizar(bool $obrigado, array $motivos): void {
        $this->dados['resultado'] = [
            'obrigado_a_declarar' => $obrigado,
            'motivos_ativados' => empty($motivos) ? ['Nenhum. Totalmente isento.'] : $motivos,
            'data_simulacao' => date('Y-m-d H:i:s')
        ];
    }

    public function exportarJson(string $caminhoArquivo): void {
        $json = json_encode($this->dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($caminhoArquivo, $json);
    }
}

// ==========================================
// 3. CAMADA DE INTERFACE (CLI View)
// ==========================================

class ConsoleUI {
    private string $red = "\e[0;31m";
    private string $green = "\e[0;32m";
    private string $blue = "\e[0;34m";
    private string $yellow = "\e[0;33m";
    private string $bold = "\e[1m";
    private string $nc = "\e[0m";

    public function imprimirCabecalho(): void {
        echo "\n{$this->blue}{$this->bold}====================================================";
        echo "\n   MOTOR DE REGRAS IRPF 2026 - MODO PRO - by Caio";
        echo "\n===================================================={$this->nc}\n\n";
    }

    public function coletarNumeroPositivo(string $prompt): float {
        while (true) {
            echo $prompt . ": ";
            $input = trim(fgets(STDIN));
            if ($input === '') return 0.0;
            $input = str_replace(',', '.', $input);
            if (is_numeric($input)) {
                $valor = (float)$input;
                if ($valor >= 0) return $valor;
                echo "{$this->red}[Erro] O valor não pode ser negativo.{$this->nc}\n";
                continue;
            }
            echo "{$this->red}[Erro] Digite apenas números válidos.{$this->nc}\n";
        }
    }

    public function coletarBooleano(string $prompt): bool {
        while (true) {
            echo $prompt . " [s/n]: ";
            $input = strtolower(trim(fgets(STDIN)));
            if ($input === 's') return true;
            if ($input === 'n') return false;
            echo "{$this->red}[Erro] Digite 's' ou 'n'.{$this->nc}\n";
        }
    }

    public function imprimirAvisoGatilho(string $motivo): void {
        echo "\n{$this->red}{$this->bold}[!] ALERTA: Você já atingiu a obrigatoriedade O LEÃO VAI TE PEGAR BURGUÊS MALDITO!{$this->nc}\n";
        echo "{$this->yellow}Gatilho: {$motivo}{$this->nc}\n";
    }

    public function imprimirResultado(bool $obrigado, array $motivos): void {
        echo "\n{$this->bold}----------------------------------------------------{$this->nc}\n";
        if ($obrigado) {
            echo "{$this->red}{$this->bold}[!] RESULTADO: OBRIGATÓRIO DECLARAR LEÃO DA RECEITA VAI TE COMER!{$this->nc}\n";
            echo "Resumo dos gatilhos ativados:\n";
            foreach ($motivos as $m) {
                echo "  -> {$m}\n";
            }
        } else {
            echo "{$this->green}{$this->bold}[VITÓRIA] STATUS: TOTALENTE ISENT0, DIGA SIM A SONEGAÇÃO IMPOSTO É ROUBO!{$this->nc}\n";
            echo "Nenhum gatilho da Receita foi ativado.\n";
        }
        echo "{$this->bold}----------------------------------------------------{$this->nc}\n";
        echo "{$this->blue}>> Relatório salvo em 'relatorio_irpf_2026.json' na raiz.{$this->nc}\n\n";
    }
}

// ==========================================
// 4. """"""""Main""""""""
// ==========================================

$ui = new ConsoleUI();
$relatorio = new RelatorioIrpf();

$ui->imprimirCabecalho();

$pipeline = [
    'renda_tributavel' => ['pergunta' => "[Caso 1] Renda Tributável (Salários/Freelas) Quanto você Ganhou no ano de 2025?", 'regra' => new RegraRenda()],
    'rendimentos_isentos' => ['pergunta' => "[Caso 2] Rendimentos Isentos (Poupança, CDI) Referente ao ano de 2025" , 'regra' => new RegraIsentos()],
    'bens_direitos' => ['pergunta' => "[Caso 3] Total de Bens (Imóveis/Carros) Referente ao ano de 2025", 'regra' => new RegraBens()],
    'ganhos_bets' => ['pergunta' => "[Caso 4] Lucro líquido com Bets/Apostas, Referente ao ano de 2025", 'regra' => new RegraBets()]
];

$obrigado = false;
$motivosAtivados = [];

foreach ($pipeline as $chave => $etapa) {
    $valorDigitado = $ui->coletarNumeroPositivo($etapa['pergunta']);
    $relatorio->registrarEntrada($chave, $valorDigitado);

    /** @var RegraIrpf $regra */
    $regra = $etapa['regra'];

    if ($regra->verificar($valorDigitado)) {
        $motivoAtual = $regra->getMotivo($valorDigitado);
        $motivosAtivados[] = $motivoAtual;

        // Se é a primeira vez que ele fica obrigado, disparamos o aviso e perguntamos
        if (!$obrigado) {
            $obrigado = true;
            $ui->imprimirAvisoGatilho($motivoAtual);

            $continuar = $ui->coletarBooleano("Deseja continuar preenchendo as outras categorias?");
            if (!$continuar) {
                break; // Aqui sim, respeitamos a decisão do usuário de parar
            }
            echo "\n"; // Quebra de linha para manter o console limpo se ele continuar
        }
    }
}

// Finaliza relatório com todos os motivos acumulados
$relatorio->finalizar($obrigado, $motivosAtivados);
$relatorio->exportarJson(__DIR__ . '/relatorio_irpf_2026.json');

$ui->imprimirResultado($obrigado, $motivosAtivados);