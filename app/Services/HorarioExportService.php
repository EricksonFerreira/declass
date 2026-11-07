<?php

namespace App\Services;

use App\Models\HorarioSlot;
use App\Models\Turma;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exportacao das grades horarias: XLSX com uma aba por turma e
 * dados para o PDF consolidado. O modo "professor" inclui o nome
 * do professor na celula; o padrao exibe so a disciplina.
 */
class HorarioExportService
{
    public function __construct(
        protected TurmaGradeService $gradeService,
    ) {}

    /**
     * Monta a planilha: uma aba por turma com a grade semanal.
     */
    public function planilha(Collection $turmas, bool $comProfessor = false): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        foreach ($turmas as $turma) {
            $planilha = $spreadsheet->createSheet();
            $this->preencherAba($planilha, $turma, $comProfessor);
            $nome = $this->nomeAba($turma->nome);
            $planilha->setTitle($nome);
        }

        if ($spreadsheet->getSheetCount() === 0) {
            $planilha = $spreadsheet->createSheet();
            $planilha->setTitle('Sem turmas');
            $planilha->setCellValue('A1', 'Nenhuma turma encontrada.');
        }

        return $spreadsheet;
    }

    /** Preenche uma aba com a grade da turma em formato matriz. */
    private function preencherAba(Worksheet $sheet, Turma $turma, bool $comProfessor): void
    {
        $export = $this->gradeService->gradeParaExportacao($turma, $comProfessor);
        $grade = $export['grade'];
        $intervalos = $export['intervalos'];
        $dias = HorarioSlot::DIAS;

        $diasGrade = array_keys($grade);
        $ordens = [];
        foreach ($grade as $aulas) {
            foreach ($aulas as $ordem => $aula) {
                $ordens[$ordem] = true;
            }
        }
        $ordens = array_keys($ordens);
        sort($ordens);

        $sheet->setCellValue('A1', 'Grade horária — '.$turma->nome
            .($turma->escola ? ' · '.$turma->escola->nome : '')
            .($turma->turno ? ' · '.(\App\Models\Turma::TURNOS[$turma->turno] ?? $turma->turno) : ''));
        $ultimaCol = chr(ord('A') + count($diasGrade));
        $sheet->mergeCells('A1:'.$ultimaCol.'1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $linha = 3;
        $sheet->setCellValue('A'.$linha, 'Horário');
        $col = 2;
        foreach ($diasGrade as $dia) {
            $sheet->setCellValue([$col, $linha], \App\Models\HorarioSlot::diaLabel((int) $dia));
            $col++;
        }
        $sheet->getStyle('A'.$linha.':'.$ultimaCol.$linha)->getFont()->setBold(true);
        $linha++;

        foreach ($ordens as $ordem) {
            // Rotulo da linha com o horario do primeiro dia que tiver a aula.
            $horario = '';
            foreach ($diasGrade as $dia) {
                if (isset($grade[$dia][$ordem])) {
                    $horario = $grade[$dia][$ordem]['inicio'].'–'.$grade[$dia][$ordem]['fim'];
                    break;
                }
            }
            $sheet->setCellValue('A'.$linha, $ordem.'ª aula'.($horario ? " ({$horario})" : ''));
            $col = 2;
            foreach ($diasGrade as $dia) {
                $rotulo = $grade[$dia][$ordem]['rotulo'] ?? '';
                $sheet->setCellValue([$col, $linha], $rotulo !== '' ? $rotulo : '');
                $col++;
            }
            $linha++;

            // Linha de intervalo logo abaixo da aula anterior ao recreio.
            if (collect($intervalos)->contains(fn ($porDia) => isset($porDia[$ordem]))) {
                $iv = collect($intervalos)->map(fn ($porDia) => $porDia[$ordem] ?? null)->filter()->first();
                $sheet->setCellValue('A'.$linha, 'Intervalo'.($iv ? " ({$iv['inicio']}–{$iv['fim']})" : ''));
                $col = 2;
                foreach ($diasGrade as $dia) {
                    $sheet->setCellValue([$col, $linha], isset($intervalos[$dia][$ordem]) ? 'Intervalo' : '');
                    $col++;
                }
                $linha++;
            }
        }

        $sheet->getColumnDimension('A')->setWidth(22);
        for ($i = 2; $i <= 1 + count($diasGrade); $i++) {
            $sheet->getColumnDimension(chr(64 + $i))->setWidth(24);
        }

        $sheet->getStyle('A4:'.$ultimaCol.$linha)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
    }

    /** Nome de aba: Excel limita a 31 chars e proibe : \ / ? * [ ]. */
    private function nomeAba(string $nome): string
    {
        $nome = preg_replace('/[:\\\\\/\?\*\[\]]/', '', $nome);
        $nome = trim((string) $nome);

        return mb_substr($nome !== '' ? $nome : 'Turma', 0, 31);
    }

    /**
     * Dados para o PDF consolidado: uma grade por turma.
     */
    public function dadosPdf(Collection $turmas, bool $comProfessor = false): array
    {
        return $turmas->map(fn (Turma $t) => [
            'turma' => $t,
            'export' => $this->gradeService->gradeParaExportacao($t, $comProfessor),
        ])->all();
    }
}
