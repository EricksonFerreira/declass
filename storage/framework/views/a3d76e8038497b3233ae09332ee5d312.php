<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('adminlte.title', 'Sistema')); ?> — Começar</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            background: #f0f2f6;
            color: #111827;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.25rem 1rem;
        }
        .wrap { width: 100%; max-width: 640px; }
        .card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 0 0 1px rgba(0,0,0,.05), 0 4px 6px rgba(0,0,0,.04), 0 16px 48px rgba(0,0,0,.09);
            overflow: hidden;
        }
        .head { padding: 1.6rem 1.8rem 1.25rem; border-bottom: 1px solid #eef2f7; }
        .title { font-size: 1.15rem; font-weight: 800; letter-spacing: -.02em; margin-bottom: .35rem; }
        .sub { font-size: .85rem; color: #64748b; line-height: 1.5; }
        .body { padding: 1.6rem 1.8rem 1.8rem; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        @media (max-width: 640px) { .grid { grid-template-columns: 1fr; } }
        label { font-size: .78rem; font-weight: 700; color: #0f172a; margin-bottom: 6px; display: block; }
        input {
            width: 100%;
            height: 42px;
            padding: 0 .85rem;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            font-size: .9rem;
            outline: none;
            background: #fff;
        }
        input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
        .row { margin-bottom: 12px; }
        .req { color: #ef4444; margin-left: 3px; }
        .alert {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #9f1239;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: .82rem;
            margin-bottom: 14px;
        }
        .actions { display: flex; justify-content: space-between; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
        .btn {
            height: 44px;
            border-radius: 12px;
            padding: 0 1rem;
            font-weight: 800;
            font-size: .86rem;
            border: 1px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: .15s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            border-color: #2563eb;
            color: #fff;
            box-shadow: 0 12px 24px rgba(37,99,235,.18);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 16px 30px rgba(37,99,235,.22); }
        .btn-ghost { background: #fff; border-color: #e5e7eb; color: #111827; }
        .btn-ghost:hover { border-color: #cbd5e1; }
        .note {
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            font-size: .82rem;
            line-height: 1.55;
        }
        .note strong { color: #0f172a; }
    </style>
</head>

<body>
<div class="wrap">
    <div class="card">
        <div class="head">
            <div class="title">Começar agora</div>
            <div class="sub">
                Preencha os dados principais da sua empresa. Ao final, o sistema cria um usuario unico com senha padrao
                (no primeiro login voce sera obrigado a definir uma nova senha).
            </div>
        </div>

        <div class="body">
            <?php if($errors->any()): ?>
                <div class="alert">
                    <strong>Corrija os campos abaixo:</strong>
                    <ul style="margin:.4rem 0 0 1.1rem;">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <li><?php echo e($e); ?></li> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('cadastro.empresa.store')); ?>">
                <?php echo csrf_field(); ?>

                <div class="row">
                    <label>Empresa <span class="req">*</span></label>
                    <input id="empresa_nome" name="empresa_nome" value="<?php echo e(old('empresa_nome')); ?>" placeholder="Nome da locadora" required>
                </div>

                <div class="grid">
                    <div class="row">
                        <label>CNPJ</label>
                        <input id="empresa_cnpj" name="empresa_cnpj" value="<?php echo e(old('empresa_cnpj')); ?>" placeholder="00.000.000/0000-00">
                    </div>
                    <div class="row">
                        <label>Telefone</label>
                        <input id="empresa_telefone" name="empresa_telefone" value="<?php echo e(old('empresa_telefone')); ?>" placeholder="(00) 00000-0000">
                    </div>
                </div>

                <div class="row">
                    <label>E-mail da empresa</label>
                    <input id="empresa_email" name="empresa_email" type="email" value="<?php echo e(old('empresa_email')); ?>" placeholder="financeiro@empresa.com">
                </div>

                <hr style="border:0;border-top:1px solid #eef2f7;margin:14px 0;">

                <div class="grid">
                    <div class="row">
                        <label>Responsavel <span class="req">*</span></label>
                        <input id="admin_nome" name="admin_nome" value="<?php echo e(old('admin_nome')); ?>" placeholder="Nome do responsavel" required>
                    </div>
                    <div class="row">
                        <label>E-mail do responsavel <span class="req">*</span></label>
                        <input id="admin_email" name="admin_email" type="email" value="<?php echo e(old('admin_email')); ?>" placeholder="voce@empresa.com" required>
                    </div>
                </div>

                <input type="hidden" name="plano" value="<?php echo e(old('plano', $planoPreferido ?? '')); ?>">
                <input type="hidden" name="sem_site" value="<?php echo e(old('sem_site', !empty($semSite) ? '1' : '0')); ?>">

                <div class="note">
                    <strong>Importante:</strong> apos o cadastro, voce conseguira acessar o painel, mas os modulos ficam bloqueados
                    ate o pagamento do plano ser confirmado.
                </div>

                <div class="actions">
                    <a class="btn btn-ghost" href="<?php echo e(route('site.apresentacao')); ?>">Voltar</a>
                    <button type="submit" class="btn btn-primary">Criar acesso</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
// ── Validação de CNPJ ──
function validarCnpj(value) {
    var cnpj = value.replace(/[^0-9]/g, '');
    if (cnpj.length !== 14) return false;
    if (/^(\d)\1{13}$/.test(cnpj)) return false;

    var soma = 0, peso = [5,4,3,2,9,8,7,6,5,4,3,2];
    for (var i = 0; i < 12; i++) soma += parseInt(cnpj[i]) * peso[i];
    var dig1 = soma % 11 < 2 ? 0 : 11 - (soma % 11);
    if (parseInt(cnpj[12]) !== dig1) return false;

    soma = 0; peso = [6,5,4,3,2,9,8,7,6,5,4,3,2];
    for (var i = 0; i < 13; i++) soma += parseInt(cnpj[i]) * peso[i];
    var dig2 = soma % 11 < 2 ? 0 : 11 - (soma % 11);
    if (parseInt(cnpj[13]) !== dig2) return false;

    return true;
}

function validarTelefone(value) {
    var nums = value.replace(/[^0-9]/g, '');
    return nums.length === 10 || nums.length === 11;
}

function mostrarErro(input, msg) {
    var err = input.parentNode.querySelector('.field-error');
    if (!err) {
        err = document.createElement('div');
        err.className = 'field-error';
        err.style.cssText = 'color:#ef4444;font-size:.72rem;font-weight:600;margin-top:4px;';
        input.parentNode.appendChild(err);
    }
    err.textContent = msg;
    input.style.borderColor = '#ef4444';
}

function limparErro(input) {
    var err = input.parentNode.querySelector('.field-error');
    if (err) err.textContent = '';
    input.style.borderColor = '';
}

// ── Mascaras ──
// Valida CNPJ ao sair do campo (blur) para dar feedback mesmo com campo opcional
document.getElementById('empresa_cnpj')?.addEventListener('blur', function () {
    var nums = this.value.replace(/[^0-9]/g, '');
    if (nums.length > 0 && nums.length !== 14) {
        mostrarErro(this, 'CNPJ deve ter 14 digitos.');
    } else if (nums.length === 14 && !validarCnpj(nums)) {
        mostrarErro(this, 'CNPJ invalido.');
    } else {
        limparErro(this);
    }
});

document.getElementById('empresa_cnpj')?.addEventListener('input', function () {
    var v = this.value.replace(/[^0-9]/g, '');
    if (v.length > 14) v = v.slice(0, 14);
    var m = '';
    if (v.length > 0) m = v.slice(0, 2);
    if (v.length > 2) m += '.' + v.slice(2, 5);
    if (v.length > 5) m += '.' + v.slice(5, 8);
    if (v.length > 8) m += '/' + v.slice(8, 12);
    if (v.length > 12) m += '-' + v.slice(12, 14);
    this.value = m;
    if (v.length === 14) {
        if (!validarCnpj(v)) mostrarErro(this, 'CNPJ invalido.');
        else limparErro(this);
    } else {
        limparErro(this);
    }
});

document.getElementById('empresa_telefone')?.addEventListener('input', function () {
    var v = this.value.replace(/[^0-9]/g, '');
    if (v.length > 11) v = v.slice(0, 11);
    var m = '';
    if (v.length > 0) m = '(' + v.slice(0, 2);
    if (v.length > 2) m += ') ' + v.slice(2, 7);
    if (v.length > 7) m += '-' + v.slice(7, 11);
    this.value = m;
    if (v.length > 0) {
        if (!validarTelefone(v)) mostrarErro(this, 'Telefone invalido. Use (XX) XXXXX-XXXX.');
        else limparErro(this);
    } else {
        limparErro(this);
    }
});

// ── Validacao de email no blur ──
['empresa_email', 'admin_email'].forEach(function (id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('blur', function () {
        var v = this.value.trim();
        if (v && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
            mostrarErro(this, 'E-mail invalido.');
        } else {
            limparErro(this);
        }
    });
});

// ── Validacao no submit ──
document.querySelector('form').addEventListener('submit', function (e) {
    var cnpj = document.getElementById('empresa_cnpj');
    if (cnpj && cnpj.value) {
        var nums = cnpj.value.replace(/[^0-9]/g, '');
        if (nums.length > 0 && !validarCnpj(nums)) {
            e.preventDefault();
            mostrarErro(cnpj, 'CNPJ invalido.');
            cnpj.focus();
            return;
        }
    }

    var tel = document.getElementById('empresa_telefone');
    if (tel && tel.value) {
        var nums = tel.value.replace(/[^0-9]/g, '');
        if (nums.length > 0 && !validarTelefone(nums)) {
            e.preventDefault();
            mostrarErro(tel, 'Telefone invalido. Use (XX) XXXXX-XXXX.');
            tel.focus();
            return;
        }
    }

    ['empresa_email', 'admin_email'].forEach(function (id) {
        var el = document.getElementById(id);
        if (!el || !el.value) return;
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value.trim())) {
            e.preventDefault();
            mostrarErro(el, 'E-mail invalido.');
            el.focus();
            return;
        }
    });
});
</script>
</body>
</html>

<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\auth\cadastro-empresa.blade.php ENDPATH**/ ?>