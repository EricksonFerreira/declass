<!-- Modal -->
<style>
    .select2-selection__rendered{
        font-size:13px;
    }
</style>
<div class="modal fade" id="detalhesDanoModal" tabindex="-1" role="dialog" aria-labelledby="detalhesDanoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="detalhesDanoModalLabel"><strong>Detalhes do Dano:</strong> <span id="informacao_dano"></span></h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form_dano">
                    <div class="form-row" id="mensagem_erro">
                        {{-- essas mensagem virão do js ajax --}}

                    </div>
                    <div class="form-row">
                        <div class="col">
                            <x-form-select id="categoria_problema" name="categoria_problema"
                            label="Categoria do Problema" :options="$categoriaDanos" placeholder="Selecione uma categoria"
                            :selected="old(
                                'categoria_problema',
                                null,
                                )" class="form-control form-control-sm" required  onchange="obterDanos()"/>
                        </div>
                        {{-- <label for="categoria-problema">Categoria do Problema</label>
                        <select class="form-control form-control-sm" id="categoria-problema" name="categoria-problema">
                            @foreach($categoriaDanos as $categoria)
                                <option value="{{$categoria->id}}">{{$categoria->nome}}</option>
                            @endforeach
                        </select> --}}
                        <div class="col">
                            <label for="localizacao-problema">Localização do Problema</label>
                            <select class="form-control form-control-sm" id="localizacao_problema" onchange="obterDanos()" name="localizacao-problema">
                                <option name="exterior">Exterior</option>
                                <option name="interior">Interior</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="danos">Danos <small class="text-danger">*</small></label>
                            <img id="loading_dano" style="width:11px; height:11px; display:none" src="{{asset('assets/gifs/loading.gif')}}" alt="Carregando...">
                        </div>
                        <select name="dano" id="danos" class="form-control form-control-sm select2" required>
                            <option disabled selected>Primeiro selecione uma categoria</option>
                        </select>
                    </div>
                    <hr>
                    <div class="form-row">
                        <div class="col-md-6">
                            <label for="detalhes-problema">Tipo <small class="text-danger">*</small></label>
                            <div class="form-check">
                                <input type="radio" checked class="form-check-input" name="tipo" value="ausente">
                                <label class="form-check-label">Ausente</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" class="form-check-input" name="tipo" value="amolgadela leve">
                                <label class="form-check-label">Amolgadela leve</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" class="form-check-input" name="tipo" value="amolgadela grave">
                                <label class="form-check-label">Amolgadela grave</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" class="form-check-input" name="tipo" value="risco leve">
                                <label class="form-check-label">Risco leve</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" class="form-check-input" name="tipo" value="risco grave">
                                <label class="form-check-label">Risco grave</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col">
                                <label for="custo-estimado">Custo Estimado <span class="text-danger">*</span></label>
                                <input type="text" required class="form-control form-control-sm" id="custo_estimado" name="custo_estimado">
                            </div>
                            <div class="col">
                                <label for="quilometragem-dano">Quilometragem <span class="text-danger">*</span></label>
                                <input type="number" required class="form-control form-control-sm" id="quilometragem_dano" name="quilometragem_dano">
                            </div>
                        </div>
                    </div>
                    <div class="form-row mt-2">
                        <label for="observacoes-dano">Observações</label>
                        <textarea class="form-control form-control-sm" id="observacoes_dano" name="observacoes_dano"></textarea>
                    </div>
                    <div class="form-row">
                        <label for="fotos-dano">Fotos</label>
                        <input type="file" class="form-control-file" id="fotos-dano" name="fotos_dano" multiple>
                    </div>
                    <div class="form-row mt-2" style="display:none" id="div_previsualizar">
                        <label for="previsualizar_imagens">Pré-visualização das Imagens</label>
                        <div id="previsualizar_imagens" class="d-flex flex-wrap"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-xs btn-primary"> <i class="fa fa-save"></i> Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="{{asset('js/sweetAlert.min.js')}}"></script>
<script>
    document.getElementById('form_dano').addEventListener('submit', function(event) {
        event.preventDefault();
        const campos = document.querySelectorAll('#form_dano input[required], #form_dano select[required], #form_dano textarea[required]');
        campos.forEach(campo => {
            if (!campo.value) {
            campo.classList.add('is-invalid');
            } else {
            campo.classList.remove('is-invalid');
            }
        });
        adicionarDanoVeiculo();
    });

    function adicionarDanoVeiculo(){
        const dano = {
            categoria: document.getElementById('categoria_problema') ? document.getElementById('categoria_problema').value : null,
            local_dano: document.getElementById('informacao_dano') ? document.getElementById('informacao_dano').innerHTML.normalize('NFD').replace(/[\u0300-\u036f]/g, "").toLowerCase() : null,
            localizacao_problema: document.getElementById('localizacao_problema') ? document.getElementById('localizacao_problema').value : null,
            dano: document.getElementById('danos') ? document.getElementById('danos').value : null,
            tipo: document.querySelector('input[name="tipo"]:checked') ? document.querySelector('input[name="tipo"]:checked').value : null,
            custo_estimado: document.getElementById('custo_estimado') ? document.getElementById('custo_estimado').value : null,
            quilometragem: document.getElementById('quilometragem_dano') ? document.getElementById('quilometragem_dano').value : null,
            observacoes: document.getElementById('observacoes_dano') ? document.getElementById('observacoes_dano').value : null,
            fotos: document.getElementById('fotos-dano') ? document.getElementById('fotos-dano').files : [],
            veiculo: document.getElementById('veiculo') ? document.getElementById('veiculo').value : null,
        };

        const reservaId = document.getElementById('reserva_id').value;
        url =  "{{ url('reserva') }}/" + reservaId + "/adicionarDanoVeiculo";

        const formData = new FormData();
        formData.append('categoria_id', dano.categoria);
        formData.append('localizacao_problema', dano.localizacao_problema);
        if(dano.dano.typeof == 'int'){
            formData.append('dano_id', dano.dano);
        }
        formData.append('tipo_dano', dano.tipo);

        if(dano.local_dano){
            formData.append('local_dano', dano.local_dano);
        }
        formData.append('custo_estimado', dano.custo_estimado);
        formData.append('quilometragem_veiculo', dano.quilometragem);

        if(dano.observacoes){
            formData.append('observacoes', dano.observacoes);
        }
        formData.append('veiculo_id', dano.veiculo);
        Array.from(dano.fotos).forEach((file, index) => {
            formData.append('fotos_dano[]', file);
        });

        csrf = document.querySelector('meta[name="csrf-token"]').content;

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': csrf
            },
            processData: false,
            contentType: false,
            success: function(response){
                $('#detalhesDanoModal').modal('hide');
                document.getElementById('categoria_problema').value = null;
                document.getElementById('informacao_dano').innerText = '';
                document.getElementById('localizacao_problema').value = null;
                document.getElementById('danos').innerHTML = '<option disabled selected>Primeiro selecione uma categoria</option>';
                document.querySelectorAll('input[name="tipo"]').forEach(input => input.checked = false);
                document.querySelector('input[name="tipo"][value="ausente"]').checked = true;
                document.getElementById('custo_estimado').value = null;
                document.getElementById('quilometragem_dano').value = null;
                document.getElementById('observacoes_dano').value = null;
                document.getElementById('fotos-dano').value = null;
                document.getElementById('previsualizar_imagens').innerHTML = '';
                document.getElementById('div_previsualizar').style.display = 'none';

                Swal.fire({
                    icon: 'success',
                    title: 'Dano adicionado com sucesso!',
                    showConfirmButton: false,
                    timer: 1500,
                    toast: true,
                    position: 'top-right',
                    customClass: {
                        title: 'small-font'
                    },
                    showConfirmButton: false,
                    timer: 1500
                });

                obterDanosDetectados();
                obterDanosExistentes();
            },
            error: function(response){
                $('#mensagem_erro').html('');

                const errors = response.responseJSON.errors;
                let errorList = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Erro(s) encontrado(s):</strong>
                        <ul class="m-0">
                `;
                for (const key in errors) {
                    if (errors.hasOwnProperty(key)) {
                        errors[key].forEach(err => {
                            errorList += `<li>${err}</li>`;
                        });
                    }
                }
                errorList += `
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;
                $('#mensagem_erro').append(errorList);

            }
        });
    }



    document.querySelectorAll('.img-fluid').forEach(img => {
        img.addEventListener('click', () => {
            $('#detalhesDanoModal').modal('show');
        });
    });

    document.getElementById('fotos-dano').addEventListener('change', function(event) {
        const previewContainer = document.getElementById('previsualizar_imagens');
        previewContainer.innerHTML = '';
        const files = event.target.files;
        const div_previsualizar = document.getElementById('div_previsualizar');

        if (files) {
            div_previsualizar.style.display = 'block';

            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('img-thumbnail', 'm-2');
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.cursor = 'zoom-in';
                    previewContainer.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        }else{
            div_previsualizar.style.display = 'none';
        }
    });
    document.getElementById('previsualizar_imagens').addEventListener('click', function(event) {
        if (event.target.tagName === 'IMG') {
            const imgSrc = event.target.src;
            const zoomModal = document.createElement('div');
            zoomModal.classList.add('modal', 'fade');
            zoomModal.id = 'zoomModal';
            zoomModal.tabIndex = -1;
            zoomModal.role = 'dialog';
            zoomModal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="${imgSrc}" class="img-fluid">
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(zoomModal);
            $('#zoomModal').modal('show');
            $('#zoomModal').on('hidden.bs.modal', function () {
                document.body.removeChild(zoomModal);
            });
        }
    });

    function setNomeDano(nome){
        document.getElementById('informacao_dano').innerText = nome;
    }
    function obterDanos(){
        categoria = document.getElementById('categoria_problema').value;
        localizacao = document.getElementById('localizacao_problema').value;

        if(!categoria || !localizacao){
            return;
        }
        $('#loading_dano').show();
        $.ajax({
            url: "{{ route('dano.obterDanosPorCategoriaELocalizacao') }}",
            type: 'GET',
            data: {
                categoria: categoria,
                localizacao: localizacao
            },
            success: function(response){
                $('#danos').empty();
                if(response.length === 0){
                    $('#danos').append('<option disabled>Nenhum dano encontrado</option>');
                }else{

                    $('#danos').append('<option disabled selected>Selecione um dano</option>');
                    response.forEach(dano => {
                        $('#danos').append(`<option value="${dano.id}">${dano.nome}</option>`);
                    });
                }
                $('#loading_dano').hide();
            },
            error: function(response){
                console.log(response);
                $('#loading_dano').hide();
            }
        });
    }
</script>
