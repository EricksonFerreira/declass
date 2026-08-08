$path = "c:\Users\Erickson\Dev\laragon\www\clockify\app\Http\Controllers"
$patterns = @(
    "Veiculo", "Frota", "GrupoFrota", "Checklist", "Multa", "Sinistro", "Dano",
    "Fipe", "Os", "OrdemServico", "Manutencao", "CheckIn", "Estacao", "Caracteristica",
    "Avaria"
)

Get-ChildItem -Path $path -Filter "*Controller.php" | ForEach-Object {
    $name = $_.Name
    $shouldDelete = $false
    foreach ($p in $patterns) {
        if ($name -like "*$p*") {
            $shouldDelete = $true
            break
        }
    }
    if ($shouldDelete) {
        Remove-Item $_.FullName -Force -Verbose
    }
}
