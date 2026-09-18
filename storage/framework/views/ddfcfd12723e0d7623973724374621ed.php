<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('adminlte.title', 'Sistema')); ?> — Confirmar senha</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html,body{min-height:100%;font-family:'Inter',system-ui,sans-serif;background:#f0f2f6;color:#111827;-webkit-font-smoothing:antialiased}
        .page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem}
        .wrap{width:100%;max-width:400px;display:flex;flex-direction:column;align-items:center;gap:1.5rem}
        .logo{display:flex;align-items:center;gap:.6rem}
        .logo__mark{width:38px;height:38px;border-radius:11px;background:#1e4fd8;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px rgba(30,79,216,.35)}
        .logo__name{font-size:1.15rem;font-weight:600;color:#111827;letter-spacing:-.02em}
        .card{background:#fff;border-radius:18px;width:100%;box-shadow:0 0 0 1px rgba(0,0,0,.05),0 4px 6px rgba(0,0,0,.04),0 16px 48px rgba(0,0,0,.09);overflow:hidden}
        .card__body{padding:2.25rem 2.375rem 2rem}
        .fhead{margin-bottom:1.5rem}
        .fhead__title{font-size:1.2rem;font-weight:600;color:#111827;letter-spacing:-.025em;line-height:1.3;margin-bottom:.3rem}
        .fhead__sub{font-size:.815rem;color:#6b7280;line-height:1.6}
        .notice{display:flex;gap:.75rem;padding:1rem 1.1rem;background:#f8f9fc;border:1.5px solid #e9eaee;border-radius:10px;margin-bottom:1.5rem}
        .notice__icon{flex-shrink:0;color:#1e4fd8;margin-top:1px}
        .notice__text{font-size:.815rem;color:#374151;line-height:1.6}
        .field{display:flex;flex-direction:column;gap:.35rem}
        .field__label{font-size:.775rem;font-weight:500;color:#374151;letter-spacing:.005em}
        .field__error{font-size:.74rem;color:#e53e3e;line-height:1.4}
        .input-wrap{position:relative;display:flex;align-items:center}
        .input-wrap__icon{position:absolute;left:.85rem;color:#cbd5e1;display:flex;align-items:center;pointer-events:none;transition:color .15s}
        .input-wrap:focus-within .input-wrap__icon{color:#1e4fd8}
        .input{width:100%;height:43px;padding:0 2.6rem 0 2.55rem;font-size:.855rem;font-family:'Inter',sans-serif;color:#111827;background:#f8f9fc;border:1.5px solid #e9eaee;border-radius:10px;outline:none;transition:border-color .15s,background .15s,box-shadow .15s;-webkit-appearance:none}
        .input::placeholder{color:#c5ccd7}
        .input:hover{border-color:#d1d5db;background:#f3f4f8}
        .input:focus{border-color:#1e4fd8;background:#fff;box-shadow:0 0 0 3.5px rgba(30,79,216,.1)}
        .field--error .input{border-color:#f05252;background:#fef7f7}
        .field--error .input:focus{box-shadow:0 0 0 3.5px rgba(240,82,82,.1)}
        .field--error .input-wrap__icon{color:#f05252}
        .eye-btn{position:absolute;right:.7rem;background:none;border:none;padding:5px;cursor:pointer;color:#c5ccd7;display:flex;align-items:center;border-radius:6px;transition:color .15s,background .15s}
        .eye-btn:hover{color:#6b7280;background:#f3f4f6}
        .btn{width:100%;height:44px;margin-top:1.5rem;display:flex;align-items:center;justify-content:center;gap:.45rem;background:#1e4fd8;color:#fff;font-size:.875rem;font-weight:600;font-family:'Inter',sans-serif;letter-spacing:.01em;border:none;border-radius:10px;cursor:pointer;transition:background .15s,transform .1s,box-shadow .15s;box-shadow:0 1px 3px rgba(30,79,216,.3),0 4px 14px rgba(30,79,216,.2)}
        .btn:hover{background:#1338b0;transform:translateY(-1px);box-shadow:0 2px 6px rgba(30,79,216,.35),0 8px 22px rgba(30,79,216,.22)}
        .btn:active{transform:translateY(0);background:#1030a0}
    </style>
</head>
<body>
<div class="page">
    <div class="wrap">

        <div class="logo">
            <div class="logo__mark">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M10 1L18.5 6v8L10 19 1.5 14V6L10 1z" fill="white" fill-opacity=".95"/>
                    <path d="M10 5.5L15 8.5v6L10 17 5 14.5v-6L10 5.5z" fill="white" fill-opacity=".28"/>
                </svg>
            </div>
            <span class="logo__name"><?php echo e(config('adminlte.title', 'Sistema')); ?></span>
        </div>

        <div class="card">
            <div class="card__body">

                <div class="fhead">
                    <h1 class="fhead__title">Área segura</h1>
                </div>

                <div class="notice">
                    <span class="notice__icon">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M8 1.5L2 4v4c0 3.314 2.686 6 6 6s6-2.686 6-6V4L8 1.5z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
                            <path d="M5.5 8l2 2 3-3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <p class="notice__text">Esta é uma área protegida. Confirme sua senha para continuar.</p>
                </div>

                <form method="POST" action="<?php echo e(route('password.confirm')); ?>" novalidate>
                    <?php echo csrf_field(); ?>

                    <div class="field <?php echo e($errors->has('password') ? 'field--error' : ''); ?>">
                        <label class="field__label" for="password">Senha</label>
                        <div class="input-wrap">
                            <span class="input-wrap__icon">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <rect x="2" y="6" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                                    <path d="M4.5 6V4.5a2.5 2.5 0 015 0V6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                    <circle cx="7" cy="9.5" r=".85" fill="currentColor"/>
                                </svg>
                            </span>
                            <input id="password" class="input" type="password" name="password"
                                placeholder="Sua senha atual"
                                required autocomplete="current-password">
                            <button type="button" class="eye-btn" data-target="password" tabindex="-1">
                                <svg class="eye-show" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M1 7s2.2-4 6-4 6 4 6 4-2.2 4-6 4-6-4-6-4z" stroke="currentColor" stroke-width="1.2"/><circle cx="7" cy="7" r="1.75" stroke="currentColor" stroke-width="1.2"/></svg>
                                <svg class="eye-hide" width="14" height="14" viewBox="0 0 14 14" fill="none" style="display:none"><path d="M1 7s2.2-4 6-4 6 4 6 4-2.2 4-6 4-6-4-6-4z" stroke="currentColor" stroke-width="1.2"/><circle cx="7" cy="7" r="1.75" stroke="currentColor" stroke-width="1.2"/><line x1="2" y1="2" x2="12" y2="12" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field__error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <button type="submit" class="btn">
                        Confirmar acesso
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                            <path d="M1 6.5h11M7 2l4.5 4.5L7 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
<script>
document.querySelectorAll('.eye-btn').forEach(function(btn){
    btn.addEventListener('click',function(){
        var inp=document.getElementById(this.dataset.target);
        var s=this.querySelector('.eye-show'),h=this.querySelector('.eye-hide'),v=inp.type==='text';
        inp.type=v?'password':'text';s.style.display=v?'':'none';h.style.display=v?'none':'';
    });
});
</script>
</body>
</html><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('adminlte.title', 'Sistema')); ?> — Confirmar senha</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html,body{min-height:100%;font-family:'Inter',system-ui,sans-serif;background:#f0f2f6;color:#111827;-webkit-font-smoothing:antialiased}
        .page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem}
        .wrap{width:100%;max-width:400px;display:flex;flex-direction:column;align-items:center;gap:1.5rem}
        .logo{display:flex;align-items:center;gap:.6rem}
        .logo__mark{width:38px;height:38px;border-radius:11px;background:#1e4fd8;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px rgba(30,79,216,.35)}
        .logo__name{font-size:1.15rem;font-weight:600;color:#111827;letter-spacing:-.02em}
        .card{background:#fff;border-radius:18px;width:100%;box-shadow:0 0 0 1px rgba(0,0,0,.05),0 4px 6px rgba(0,0,0,.04),0 16px 48px rgba(0,0,0,.09);overflow:hidden}
        .card__body{padding:2.25rem 2.375rem 2rem}
        .fhead{margin-bottom:1.5rem}
        .fhead__title{font-size:1.2rem;font-weight:600;color:#111827;letter-spacing:-.025em;line-height:1.3;margin-bottom:.3rem}
        .fhead__sub{font-size:.815rem;color:#6b7280;line-height:1.6}
        .notice{display:flex;gap:.75rem;padding:1rem 1.1rem;background:#f8f9fc;border:1.5px solid #e9eaee;border-radius:10px;margin-bottom:1.5rem}
        .notice__icon{flex-shrink:0;color:#1e4fd8;margin-top:1px}
        .notice__text{font-size:.815rem;color:#374151;line-height:1.6}
        .field{display:flex;flex-direction:column;gap:.35rem}
        .field__label{font-size:.775rem;font-weight:500;color:#374151;letter-spacing:.005em}
        .field__error{font-size:.74rem;color:#e53e3e;line-height:1.4}
        .input-wrap{position:relative;display:flex;align-items:center}
        .input-wrap__icon{position:absolute;left:.85rem;color:#cbd5e1;display:flex;align-items:center;pointer-events:none;transition:color .15s}
        .input-wrap:focus-within .input-wrap__icon{color:#1e4fd8}
        .input{width:100%;height:43px;padding:0 2.6rem 0 2.55rem;font-size:.855rem;font-family:'Inter',sans-serif;color:#111827;background:#f8f9fc;border:1.5px solid #e9eaee;border-radius:10px;outline:none;transition:border-color .15s,background .15s,box-shadow .15s;-webkit-appearance:none}
        .input::placeholder{color:#c5ccd7}
        .input:hover{border-color:#d1d5db;background:#f3f4f8}
        .input:focus{border-color:#1e4fd8;background:#fff;box-shadow:0 0 0 3.5px rgba(30,79,216,.1)}
        .field--error .input{border-color:#f05252;background:#fef7f7}
        .field--error .input:focus{box-shadow:0 0 0 3.5px rgba(240,82,82,.1)}
        .field--error .input-wrap__icon{color:#f05252}
        .eye-btn{position:absolute;right:.7rem;background:none;border:none;padding:5px;cursor:pointer;color:#c5ccd7;display:flex;align-items:center;border-radius:6px;transition:color .15s,background .15s}
        .eye-btn:hover{color:#6b7280;background:#f3f4f6}
        .btn{width:100%;height:44px;margin-top:1.5rem;display:flex;align-items:center;justify-content:center;gap:.45rem;background:#1e4fd8;color:#fff;font-size:.875rem;font-weight:600;font-family:'Inter',sans-serif;letter-spacing:.01em;border:none;border-radius:10px;cursor:pointer;transition:background .15s,transform .1s,box-shadow .15s;box-shadow:0 1px 3px rgba(30,79,216,.3),0 4px 14px rgba(30,79,216,.2)}
        .btn:hover{background:#1338b0;transform:translateY(-1px);box-shadow:0 2px 6px rgba(30,79,216,.35),0 8px 22px rgba(30,79,216,.22)}
        .btn:active{transform:translateY(0);background:#1030a0}
    </style>
</head>
<body>
<div class="page">
    <div class="wrap">

        <div class="logo">
            <div class="logo__mark">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M10 1L18.5 6v8L10 19 1.5 14V6L10 1z" fill="white" fill-opacity=".95"/>
                    <path d="M10 5.5L15 8.5v6L10 17 5 14.5v-6L10 5.5z" fill="white" fill-opacity=".28"/>
                </svg>
            </div>
            <span class="logo__name"><?php echo e(config('adminlte.title', 'Sistema')); ?></span>
        </div>

        <div class="card">
            <div class="card__body">

                <div class="fhead">
                    <h1 class="fhead__title">Área segura</h1>
                </div>

                <div class="notice">
                    <span class="notice__icon">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M8 1.5L2 4v4c0 3.314 2.686 6 6 6s6-2.686 6-6V4L8 1.5z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
                            <path d="M5.5 8l2 2 3-3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <p class="notice__text">Esta é uma área protegida. Confirme sua senha para continuar.</p>
                </div>

                <form method="POST" action="<?php echo e(route('password.confirm')); ?>" novalidate>
                    <?php echo csrf_field(); ?>

                    <div class="field <?php echo e($errors->has('password') ? 'field--error' : ''); ?>">
                        <label class="field__label" for="password">Senha</label>
                        <div class="input-wrap">
                            <span class="input-wrap__icon">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <rect x="2" y="6" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                                    <path d="M4.5 6V4.5a2.5 2.5 0 015 0V6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                    <circle cx="7" cy="9.5" r=".85" fill="currentColor"/>
                                </svg>
                            </span>
                            <input id="password" class="input" type="password" name="password"
                                placeholder="Sua senha atual"
                                required autocomplete="current-password">
                            <button type="button" class="eye-btn" data-target="password" tabindex="-1">
                                <svg class="eye-show" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M1 7s2.2-4 6-4 6 4 6 4-2.2 4-6 4-6-4-6-4z" stroke="currentColor" stroke-width="1.2"/><circle cx="7" cy="7" r="1.75" stroke="currentColor" stroke-width="1.2"/></svg>
                                <svg class="eye-hide" width="14" height="14" viewBox="0 0 14 14" fill="none" style="display:none"><path d="M1 7s2.2-4 6-4 6 4 6 4-2.2 4-6 4-6-4-6-4z" stroke="currentColor" stroke-width="1.2"/><circle cx="7" cy="7" r="1.75" stroke="currentColor" stroke-width="1.2"/><line x1="2" y1="2" x2="12" y2="12" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="field__error"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <button type="submit" class="btn">
                        Confirmar acesso
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                            <path d="M1 6.5h11M7 2l4.5 4.5L7 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
<script>
document.querySelectorAll('.eye-btn').forEach(function(btn){
    btn.addEventListener('click',function(){
        var inp=document.getElementById(this.dataset.target);
        var s=this.querySelector('.eye-show'),h=this.querySelector('.eye-hide'),v=inp.type==='text';
        inp.type=v?'password':'text';s.style.display=v?'':'none';h.style.display=v?'none':'';
    });
});
</script>
</body>
</html><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\auth\confirm-password.blade.php ENDPATH**/ ?>