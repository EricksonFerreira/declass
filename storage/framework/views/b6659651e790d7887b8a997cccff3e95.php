$<?php
    use Illuminate\Support\Carbon;

    $documento = $data['documento'] ?? [];
    // $empresaDados = $documento['empresa'] ?? [];
    $contrato = $documento['contrato'] ?? [];
    $plano = $documento['plano'] ?? [];
    
    // Empresa Dados
    $empresaDados['nome'] = '66.208.400 Debora Vasconcelos Caitano dos Santos Ferreira';
    $empresaDados['cnpj'] = '66.208.400/0001-0';
    $empresaDados['endereco'] = 'Rua bom Clima, Nº2';
    $empresaDados['representante_nome'] = 'Débora Vasconcelos Caitano dos Santos Ferreira';
    $empresaDados['cidade'] = 'Igarassu';
    $empresaDados['email'] = 'contato.sonyd@gmail.com';
    $empresaDados['telefone'] = '(81) 99255-4172';

    $geradoEm = Carbon::parse($contrato['gerado_em'] ?? now());
    $dataAssinatura = !empty($contrato['data_assinatura'])
        ? Carbon::parse($contrato['data_assinatura'])->format('d/m/Y')
        : $geradoEm->format('d/m/Y');

    $valor = number_format((float) ($contrato['valor'] ?? 0), 2, ',', '.');
    $limiteVeiculos = (int) ($plano['limite_veiculos'] ?? 20);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contrato <?php echo e($contrato['numero'] ?? ''); ?></title>
<style>
    @page {
        size: A4 portrait;
        margin: 22mm 20mm 20mm 20mm;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 8.2pt;
        line-height: 1.18;
        color: #111;
    }

    .documento {
        width: 100%;
    }

    h1 {
        margin: 0 0 10px;
        font-size: 14pt;
        line-height: 1.05;
        font-weight: 800;
        text-transform: uppercase;
    }

    h2 {
        margin: 10px 0 6px;
        font-size: 9.5pt;
        line-height: 1.06;
        font-weight: 800;
        text-transform: uppercase;
        page-break-after: avoid;
    }

    p {
        margin: 0 0 5px;
        text-align: left;
        orphans: 3;
        widows: 3;
    }

    .parte {
        margin-bottom: 6px;
    }

    .label {
        font-weight: 700;
    }

    .numero {
        font-weight: 700;
    }

    .item {
        margin-left: 0;
    }

    .subitem {
        margin: 0 0 4px 16px;
    }

    .assinaturas {
        margin-top: 16px;
        page-break-inside: avoid;
    }

    .linha-assinatura {
        border-top: 1px solid #777;
        margin: 16px 0 6px;
    }

    .assinatura-bloco {
        margin-bottom: 14px;
        page-break-inside: avoid;
    }

    .assinatura-nome {
        font-weight: 700;
    }

    .local-data {
        margin-top: 12px;
        font-weight: 700;
    }

    .quebra-evitar {
        page-break-inside: avoid;
    }

    .rodape-meta {
        position: fixed;
        bottom: -13mm;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 6pt;
        color: #666;
    }
</style>
</head>
<body>
<div class="rodape-meta">
    Contrato <?php echo e($contrato['numero'] ?? '-'); ?> — gerado em <?php echo e($geradoEm->format('d/m/Y H:i')); ?>

</div>

<div class="documento">
    <h1>Contrato de Licença de Uso do Sistema DeCars – SaaS</h1>

    <p>Pelo presente instrumento particular, de um lado:</p>

    <p class="parte">
        <span class="label">CONTRATADA:</span>
        <?php echo e($empresaDados['razao_social'] ?? $empresaDados['nome'] ?? '-'); ?>, pessoa jurídica de direito privado,
        inscrita no CNPJ sob o nº <?php echo e($empresaDados['cnpj'] ?? '-'); ?>, com sede em
        <?php echo e($empresaDados['endereco'] ?? '-'); ?>, neste ato representada por
        <?php echo e($empresaDados['representante_nome'] ?? '-'); ?>, doravante denominada <span class="label">CONTRATADA</span>;
    </p>

    <p>e, de outro lado:</p>

    <p class="parte">
        <span class="label">CONTRATANTE:</span>
        <?php echo e($contrato['razao_social'] ?? '-'); ?>, pessoa jurídica de direito privado,
        inscrita no CNPJ sob o nº <?php echo e($contrato['cnpj_cliente'] ?? '-'); ?>, com sede em
        <?php echo e($contrato['endereco_cliente'] ?? '-'); ?>, neste ato representada por
        <?php echo e($contrato['representante_nome'] ?? '-'); ?>, doravante denominada <span class="label">CONTRATANTE</span>;
    </p>

    <p class="parte">
        <span class="label">CONTATO DA CONTRATANTE:</span>
        E-mail <?php echo e($contrato['email_cliente'] ?? '-'); ?> | Telefone <?php echo e($contrato['telefone_cliente'] ?? '-'); ?>

    </p>

    <p>têm entre si justo e contratado o presente <strong>Contrato de Licença de Uso do Sistema DeCars</strong>, mediante as cláusulas seguintes.</p>

    <h2>Cláusula 1ª – Do Objeto</h2>
    <p><span class="numero">1.1.</span> O presente contrato tem por objeto a concessão, pela CONTRATADA à CONTRATANTE, de licença limitada, temporária, não exclusiva, intransferível e revogável de acesso e uso do software <strong>DeCars</strong>, disponibilizado no modelo Software como Serviço – SaaS.</p>
    <p><span class="numero">1.2.</span> O DeCars é uma plataforma destinada ao auxílio na gestão de locadoras de veículos, conforme as funcionalidades disponibilizadas no plano contratado.</p>
    <p><span class="numero">1.3.</span> A licença concedida não implica venda, cessão ou transferência da propriedade do software, de seu código-fonte, marca, estrutura, documentação, banco de dados ou demais direitos de propriedade intelectual.</p>

    <h2>Cláusula 2ª – Do Plano Contratado</h2>
    <p><span class="numero">2.1.</span> A CONTRATANTE adere ao <strong><?php echo e($plano['nome'] ?? 'Plano Start'); ?></strong>, inicialmente destinado à gestão de até <strong><?php echo e($limiteVeiculos); ?> (<?php echo e($contrato['limite_veiculos_extenso'] ?? 'vinte'); ?>) veículos</strong>.</p>
    <p><span class="numero">2.2.</span> Estão incluídas na contratação as funcionalidades disponibilizadas pela CONTRATADA para o <?php echo e($plano['nome'] ?? 'plano contratado'); ?> na data da contratação.</p>
    <p><span class="numero">2.3.</span> A inclusão de veículos acima do limite contratado poderá resultar em cobrança adicional ou necessidade de alteração do plano, conforme tabela comercial vigente e mediante comunicação à CONTRATANTE.</p>
    <p><span class="numero">2.4.</span> Funcionalidades, integrações, personalizações, migrações, treinamentos ou serviços que não estejam expressamente incluídos no plano contratado poderão ser contratados separadamente.</p>

    <h2>Cláusula 3ª – Do Valor e da Forma de Pagamento</h2>
    <p><span class="numero">3.1.</span> Pela utilização do DeCars, a CONTRATANTE pagará à CONTRATADA o valor mensal de <strong>R$ <?php echo e($valor); ?></strong>.</p>
    <p><span class="numero">3.2.</span> O pagamento deverá ser realizado até o dia <strong><?php echo e($contrato['dia_vencimento'] ?? '-'); ?></strong> de cada mês, por meio de <strong><?php echo e($contrato['forma_pagamento'] ?? '-'); ?></strong>.</p>
    <p><span class="numero">3.3.</span> A primeira mensalidade vencerá em <strong><?php echo e(!empty($contrato['primeiro_vencimento']) ? Carbon::parse($contrato['primeiro_vencimento'])->format('d/m/Y') : '-'); ?></strong>.</p>
    <p><span class="numero">3.4.</span> O valor da mensalidade poderá ser reajustado anualmente, contado da data de início da contratação, com base no IPCA ou em outro índice oficial que venha a substituí-lo.</p>
    <p><span class="numero">3.5.</span> Alterações de preço decorrentes da mudança de plano, aumento da quantidade de veículos, contratação de módulos ou serviços adicionais não serão consideradas reajuste anual.</p>

    <h2>Cláusula 4ª – Da Vigência e Renovação</h2>
    <p><span class="numero">4.1.</span> Este contrato entra em vigor na data de sua assinatura e será celebrado por prazo indeterminado.</p>
    <p><span class="numero">4.2.</span> A contratação será renovada automaticamente a cada mês, mediante o pagamento da respectiva mensalidade.</p>
    <p><span class="numero">4.3.</span> Não haverá período mínimo de permanência, salvo se as partes firmarem proposta comercial ou termo adicional estabelecendo condição diferente.</p>

    <h2>Cláusula 5ª – Do Cancelamento</h2>
    <p><span class="numero">5.1.</span> Qualquer das partes poderá solicitar o encerramento do contrato a qualquer momento, mediante comunicação escrita com antecedência mínima de 30 (trinta) dias.</p>
    <p><span class="numero">5.2.</span> A solicitação poderá ser encaminhada por e-mail, WhatsApp corporativo, área de atendimento do sistema ou outro canal oficial disponibilizado pela CONTRATADA.</p>
    <p><span class="numero">5.3.</span> Durante o período de aviso prévio, o acesso permanecerá disponível e a mensalidade correspondente continuará sendo devida.</p>
    <p><span class="numero">5.4.</span> Não haverá devolução proporcional de valores referentes a períodos mensais já iniciados ou pagos.</p>
    <p><span class="numero">5.5.</span> Após o encerramento, a CONTRATANTE terá o prazo de 30 (trinta) dias para solicitar a exportação dos dados disponíveis em formato tecnicamente suportado pela plataforma.</p>
    <p><span class="numero">5.6.</span> Decorrido o prazo previsto no item anterior, os dados poderão ser eliminados ou anonimizados, ressalvadas as informações cuja conservação seja exigida por lei.</p>

    <h2>Cláusula 6ª – Do Atraso e da Inadimplência</h2>
    <p><span class="numero">6.1.</span> O atraso no pagamento poderá acarretar:</p>
    <p class="subitem">a) multa de 2% sobre o valor devido;</p>
    <p class="subitem">b) juros de mora de 1% ao mês, calculados proporcionalmente aos dias de atraso;</p>
    <p class="subitem">c) correção monetária, quando aplicável.</p>
    <p><span class="numero">6.2.</span> O acesso ao sistema poderá ser suspenso caso o pagamento permaneça em atraso por mais de 5 dias, mediante aviso à CONTRATANTE.</p>
    <p><span class="numero">6.3.</span> A suspensão do acesso não elimina a obrigação de pagamento dos valores vencidos.</p>
    <p><span class="numero">6.4.</span> Permanecendo a inadimplência por mais de 30 (trinta) dias, a CONTRATADA poderá rescindir o contrato e encerrar a conta da CONTRATANTE.</p>

    <h2>Cláusula 7ª – Das Obrigações da Contratada</h2>
    <p>São obrigações da CONTRATADA:</p>
    <p class="subitem">a) disponibilizar o acesso ao sistema conforme o plano contratado;</p>
    <p class="subitem">b) realizar manutenções corretivas e evolutivas na plataforma;</p>
    <p class="subitem">c) prestar suporte pelos canais oficiais disponibilizados;</p>
    <p class="subitem">d) adotar medidas técnicas e administrativas razoáveis para proteção dos dados;</p>
    <p class="subitem">e) comunicar, quando possível, manutenções programadas que possam causar indisponibilidade relevante;</p>
    <p class="subitem">f) manter sigilo sobre informações confidenciais acessadas em razão da prestação do serviço;</p>
    <p class="subitem">g) respeitar a legislação aplicável à proteção de dados pessoais.</p>

    <h2>Cláusula 8ª – Das Obrigações da Contratante</h2>
    <p>São obrigações da CONTRATANTE:</p>
    <p class="subitem">a) fornecer informações corretas e atualizadas para cadastro;</p>
    <p class="subitem">b) utilizar o sistema exclusivamente para finalidades lícitas e relacionadas às suas atividades;</p>
    <p class="subitem">c) efetuar os pagamentos nos prazos estabelecidos;</p>
    <p class="subitem">d) manter suas credenciais de acesso protegidas e confidenciais;</p>
    <p class="subitem">e) comunicar imediatamente qualquer acesso indevido, suspeita de fraude ou comprometimento de senha;</p>
    <p class="subitem">f) responsabilizar-se pelas informações, documentos e dados inseridos no sistema;</p>
    <p class="subitem">g) possuir base legal adequada para coletar, cadastrar e utilizar dados de clientes, funcionários, motoristas e demais titulares;</p>
    <p class="subitem">h) não tentar acessar o código-fonte, copiar, modificar, realizar engenharia reversa ou explorar vulnerabilidades do sistema;</p>
    <p class="subitem">i) não utilizar o sistema para armazenar conteúdo ilícito, ofensivo, discriminatório, fraudulento ou que viole direitos de terceiros.</p>

    <h2>Cláusula 9ª – Dos Usuários e Acessos</h2>
    <p><span class="numero">9.1.</span> A CONTRATANTE será responsável por definir quais pessoas poderão utilizar o sistema em seu nome.</p>
    <p><span class="numero">9.2.</span> Cada usuário deverá utilizar suas próprias credenciais de acesso, sendo vedado o compartilhamento de senhas.</p>
    <p><span class="numero">9.3.</span> As ações executadas por usuários cadastrados pela CONTRATANTE serão consideradas realizadas sob sua responsabilidade.</p>
    <p><span class="numero">9.4.</span> A CONTRATADA poderá bloquear acessos que apresentem comportamento suspeito, risco de segurança, violação deste contrato ou uso indevido da plataforma.</p>

    <h2>Cláusula 10ª – Do Suporte</h2>
    <p><span class="numero">10.1.</span> O suporte será prestado por meio de <strong><?php echo e($contrato['canal_suporte'] ?? 'WhatsApp e e-mail'); ?></strong>, em dias úteis, no horário de <strong><?php echo e($contrato['horario_suporte'] ?? '-'); ?></strong>.</p>
    <p><span class="numero">10.2.</span> O suporte compreende orientações de utilização, análise de falhas e esclarecimentos relacionados às funcionalidades contratadas.</p>
    <p><span class="numero">10.3.</span> Não estão incluídos no suporte padrão:</p>
    <p class="subitem">a) treinamento presencial;</p>
    <p class="subitem">b) desenvolvimento de funcionalidades exclusivas;</p>
    <p class="subitem">c) configuração de equipamentos da CONTRATANTE;</p>
    <p class="subitem">d) correção de problemas causados por internet, computadores, celulares ou serviços de terceiros;</p>
    <p class="subitem">e) importações, migrações ou ajustes em massa não previstos na contratação.</p>

    <h2>Cláusula 11ª – Da Disponibilidade do Sistema</h2>
    <p><span class="numero">11.1.</span> A CONTRATADA empregará esforços técnicos razoáveis para manter o sistema disponível e em funcionamento.</p>
    <p><span class="numero">11.2.</span> Poderão ocorrer interrupções temporárias em razão de:</p>
    <p class="subitem">a) manutenções programadas ou emergenciais;</p>
    <p class="subitem">b) falhas de internet, energia, hospedagem ou serviços de terceiros;</p>
    <p class="subitem">c) ataques cibernéticos ou eventos de segurança;</p>
    <p class="subitem">d) caso fortuito ou força maior;</p>
    <p class="subitem">e) situações necessárias para preservar a segurança e a integridade da plataforma.</p>
    <p><span class="numero">11.3.</span> A CONTRATADA não garante que o sistema funcionará de maneira ininterrupta ou totalmente livre de erros, mas deverá atuar para solucionar falhas sob sua responsabilidade em prazo razoável.</p>

    <h2>Cláusula 12ª – Das Atualizações do Software</h2>
    <p><span class="numero">12.1.</span> A CONTRATADA poderá realizar atualizações, correções, melhorias e alterações na interface ou nas funcionalidades do sistema.</p>
    <p><span class="numero">12.2.</span> As atualizações não deverão eliminar, sem justificativa, funcionalidades essenciais do plano contratado.</p>
    <p><span class="numero">12.3.</span> Novos módulos ou funcionalidades poderão ser comercializados separadamente, ainda que sejam integrados ao DeCars.</p>

    <h2>Cláusula 13ª – Da Proteção de Dados Pessoais</h2>
    <p><span class="numero">13.1.</span> As partes comprometem-se a observar a Lei nº 13.709/2018 – Lei Geral de Proteção de Dados Pessoais – LGPD.</p>
    <p><span class="numero">13.2.</span> Em relação aos dados pessoais cadastrados pela CONTRATANTE no DeCars:</p>
    <p class="subitem">a) a CONTRATANTE será, em regra, a controladora dos dados, por decidir as finalidades e os meios essenciais de seu tratamento;</p>
    <p class="subitem">b) a CONTRATADA atuará, em regra, como operadora, tratando os dados conforme as instruções da CONTRATANTE e nos limites necessários à prestação do serviço.</p>
    <p><span class="numero">13.3.</span> A CONTRATANTE declara possuir fundamento legal para o tratamento dos dados pessoais inseridos no sistema.</p>
    <p><span class="numero">13.4.</span> A CONTRATADA poderá utilizar prestadores de serviços de hospedagem, armazenamento, comunicação e infraestrutura, desde que sejam adotadas medidas adequadas de proteção.</p>
    <p><span class="numero">13.5.</span> A CONTRATADA poderá tratar dados cadastrais, registros de acesso e informações técnicas para:</p>
    <p class="subitem">a) executar este contrato;</p>
    <p class="subitem">b) garantir segurança e prevenir fraudes;</p>
    <p class="subitem">c) prestar suporte;</p>
    <p class="subitem">d) cumprir obrigações legais;</p>
    <p class="subitem">e) melhorar o funcionamento da plataforma, preferencialmente por meio de dados anonimizados ou agregados.</p>
    <p><span class="numero">13.6.</span> Cada parte será responsável pelos danos decorrentes de atos praticados em desacordo com suas obrigações legais e contratuais.</p>

    <h2>Cláusula 14ª – Da Confidencialidade</h2>
    <p><span class="numero">14.1.</span> As partes deverão manter sigilo sobre informações técnicas, comerciais, financeiras, estratégicas e operacionais recebidas em razão deste contrato.</p>
    <p><span class="numero">14.2.</span> A obrigação de confidencialidade permanecerá válida mesmo após o encerramento da contratação.</p>
    <p><span class="numero">14.3.</span> Não serão consideradas confidenciais as informações que:</p>
    <p class="subitem">a) já sejam públicas;</p>
    <p class="subitem">b) tenham sido obtidas legitimamente de terceiros;</p>
    <p class="subitem">c) devam ser apresentadas por determinação legal ou judicial.</p>

    <h2>Cláusula 15ª – Da Propriedade Intelectual</h2>
    <p><span class="numero">15.1.</span> Todos os direitos relacionados ao DeCars, incluindo marca, nome, código-fonte, estrutura, identidade visual, documentação, recursos e atualizações, pertencem exclusivamente à CONTRATADA ou aos seus licenciadores.</p>
    <p><span class="numero">15.2.</span> Este contrato concede somente o direito de acesso e utilização durante sua vigência.</p>
    <p><span class="numero">15.3.</span> É proibido copiar, vender, sublicenciar, distribuir, reproduzir ou criar software derivado do DeCars sem autorização expressa da CONTRATADA.</p>
    <p><span class="numero">15.4.</span> Os dados cadastrados pela CONTRATANTE continuarão pertencendo à CONTRATANTE, observadas as regras de armazenamento, segurança e exclusão previstas neste contrato.</p>

    <h2>Cláusula 16ª – Das Responsabilidades</h2>
    <p><span class="numero">16.1.</span> O DeCars é uma ferramenta de apoio à gestão, cabendo à CONTRATANTE revisar as informações e decisões tomadas com base no sistema.</p>
    <p><span class="numero">16.2.</span> A CONTRATADA não será responsável por:</p>
    <p class="subitem">a) informações incorretas inseridas pelos usuários;</p>
    <p class="subitem">b) decisões comerciais, administrativas, financeiras ou operacionais da CONTRATANTE;</p>
    <p class="subitem">c) prejuízos decorrentes do compartilhamento de senhas;</p>
    <p class="subitem">d) falhas de internet, equipamentos ou serviços de terceiros;</p>
    <p class="subitem">e) utilização do sistema em desacordo com este contrato;</p>
    <p class="subitem">f) multas, tributos, cobranças ou obrigações decorrentes das atividades da CONTRATANTE.</p>

    <h2>Cláusula 17ª – Da Rescisão Imediata</h2>
    <p><span class="numero">17.1.</span> O contrato poderá ser encerrado imediatamente, independentemente de aviso prévio, quando houver:</p>
    <p class="subitem">a) uso ilícito ou fraudulento do sistema;</p>
    <p class="subitem">b) tentativa de invasão, cópia ou exploração de vulnerabilidades;</p>
    <p class="subitem">c) violação de propriedade intelectual;</p>
    <p class="subitem">d) compartilhamento, revenda ou sublicenciamento não autorizado;</p>
    <p class="subitem">e) descumprimento grave das obrigações contratuais;</p>
    <p class="subitem">f) prática capaz de comprometer a segurança ou a reputação da plataforma.</p>

    <h2>Cláusula 18ª – Das Comunicações</h2>
    <p><span class="numero">18.1.</span> As comunicações relacionadas a este contrato poderão ser realizadas pelos seguintes contatos:</p>
    <div class="quebra-evitar">
        <p><strong>CONTRATADA:</strong><br>
            E-mail: <?php echo e($empresaDados['email'] ?? '-'); ?><br>
            WhatsApp: <?php echo e($empresaDados['telefone'] ?? '-'); ?></p>

        <p><strong>CONTRATANTE:</strong><br>
            E-mail: <?php echo e($contrato['email_cliente'] ?? '-'); ?><br>
            WhatsApp: <?php echo e($contrato['telefone_cliente'] ?? '-'); ?></p>
    </div>
    <p><span class="numero">18.2.</span> As partes deverão manter seus dados de contato atualizados.</p>

    <h2>Cláusula 19ª – Das Disposições Gerais</h2>
    <p><span class="numero">19.1.</span> A eventual tolerância de uma parte quanto ao descumprimento de obrigação pela outra não representará renúncia de direito ou alteração contratual.</p>
    <p><span class="numero">19.2.</span> A nulidade de uma cláusula não afetará a validade das demais disposições.</p>
    <p><span class="numero">19.3.</span> Este contrato, juntamente com a proposta comercial aceita, representa o acordo entre as partes sobre seu objeto.</p>
    <p><span class="numero">19.4.</span> Qualquer alteração relevante deverá ser formalizada por escrito, inclusive por meio eletrônico.</p>
    <p><span class="numero">19.5.</span> As partes reconhecem como válidas as assinaturas eletrônicas realizadas por plataforma de assinatura, aceite eletrônico, certificado digital ou outro meio capaz de comprovar autoria e integridade.</p>

    <h2>Cláusula 20ª – Do Foro</h2>
    <p><span class="numero">20.1.</span> Fica eleito o foro da Comarca de <strong><?php echo e($contrato['foro'] ?? $empresaDados['foro'] ?? 'Igarassu/PE'); ?></strong>, com renúncia a qualquer outro, por mais privilegiado que seja, para resolver questões decorrentes deste contrato, ressalvadas as hipóteses em que a legislação determinar foro diferente.</p>

    <div class="assinaturas">
        <p>E, por estarem de acordo, as partes assinam o presente instrumento.</p>
        <p class="local-data"><?php echo e(strtoupper($contrato['cidade_assinatura'] ?? $empresaDados['cidade'] ?? 'Igarassu')); ?>, <?php echo e($dataAssinatura); ?>.</p>

        <div class="assinatura-bloco">
            <br>
            <div class="linha-assinatura"></div>
            <div class="assinatura-nome"><?php echo e($empresaDados['razao_social'] ?? $empresaDados['nome'] ?? '-'); ?></div>
            <div>CNPJ: <?php echo e($empresaDados['cnpj'] ?? '-'); ?></div>
            <div>Representante: <?php echo e($empresaDados['representante_nome'] ?? '-'); ?></div>
        </div>

        <div class="assinatura-bloco">
            <br>
            <div class="linha-assinatura"></div>
            <div class="assinatura-nome"><?php echo e($contrato['razao_social'] ?? '-'); ?></div>
            <div>CNPJ: <?php echo e($contrato['cnpj_cliente'] ?? '-'); ?></div>
            <div>Representante: <?php echo e($contrato['representante_nome'] ?? '-'); ?></div>
        </div>
    </div>
</div>
</body>
</html>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/pdf/contratos/empresa-modelo.blade.php ENDPATH**/ ?>