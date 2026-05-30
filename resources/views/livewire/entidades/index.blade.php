<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 sm:px-0">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Entidades</h2>
                <flux:button wire:click="openCreateModal" variant="primary">
                    Nova Entidade
                </flux:button>
            </div>

            <div class="mb-4">
                <flux:input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por CNPJ/CPF, razão social, nome fantasia ou email..."
                    class="w-full"
                />
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                CNPJ/CPF
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Razão Social
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Nome Fantasia
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Email
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Tipo
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Ações</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($entidades as $entidade)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $entidade->cnpj_cpf }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $entidade->razao_social }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $entidade->nome_fantasia ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $entidade->email ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($entidade->tipo ?? [] as $t)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-violet-100 text-violet-800 dark:bg-violet-900 dark:text-violet-200">
                                                {{ ucfirst($t) }}
                                            </span>
                                        @empty
                                            -
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <flux:button.group>
                                        <flux:button.size icon="o-pencil" wire:click="openEditModal({{ $entidade->id }})" variant="ghost" class="!p-2" />
                                        <flux:button.size icon="o-trash" wire:click="delete({{ $entidade->id }})" variant="ghost" class="!p-2 text-red-600 hover:text-red-800" wire:confirm="Tem certeza que deseja excluir esta entidade?" />
                                    </flux:button.group>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                    Nenhuma entidade encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $entidades->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    <flux:modal name="entidade-modal" :show="$showModal" class="max-w-4xl w-full" @close="showModal = false">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $isEditing ? 'Editar Entidade' : 'Nova Entidade' }}
                </h3>
                <flux:button.icon icon="o-x-mark" wire:click="closeModal" variant="ghost" />
            </div>

            <!-- Tabs -->
            <flux:tabs variant="segmented" class="mb-6">
                <flux:tab :selected="$activeTab === 'dados_basicos'" wire:click="setTab('dados_basicos')">
                    Dados Básicos
                </flux:tab>
                <flux:tab :selected="$activeTab === 'endereco'" wire:click="setTab('endereco')">
                    Endereço
                </flux:tab>
                <flux:tab :selected="$activeTab === 'dados_bancarios'" wire:click="setTab('dados_bancarios')">
                    Dados Bancários
                </flux:tab>
                <flux:tab :selected="$activeTab === 'tags'" wire:click="setTab('tags')">
                    Tags
                </flux:tab>
            </flux:tabs>

            <form wire:submit.prevent="save">
                <!-- Tab: Dados Basicos -->
                @if($activeTab === 'dados_basicos')
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:fieldset>
                                <flux:label for="cnpj_cpf">CNPJ/CPF *</flux:label>
                                <flux:input wire:model="cnpj_cpf" id="cnpj_cpf" placeholder="00.000.000/0000-00 ou 000.000.000-00" />
                                @error('cnpj_cpf')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:fieldset>

                            <flux:fieldset>
                                <flux:label for="razao_social">Razão Social *</flux:label>
                                <flux:input wire:model="razao_social" id="razao_social" placeholder="Razão social da empresa" />
                                @error('razao_social')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:fieldset>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:fieldset>
                                <flux:label for="nome_fantasia">Nome Fantasia</flux:label>
                                <flux:input wire:model="nome_fantasia" id="nome_fantasia" placeholder="Nome fantasia" />
                                @error('nome_fantasia')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:fieldset>

                            <flux:fieldset>
                                <flux:label for="email">Email</flux:label>
                                <flux:input type="email" wire:model="email" id="email" placeholder="email@exemplo.com" />
                                @error('email')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:fieldset>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:fieldset>
                                <flux:label for="telefone">Telefone</flux:label>
                                <flux:input wire:model="telefone" id="telefone" placeholder="(00) 00000-0000" />
                                @error('telefone')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:fieldset>

                            <flux:fieldset>
                                <flux:label for="website">Website</flux:label>
                                <flux:input type="url" wire:model="website" id="website" placeholder="https://www.exemplo.com" />
                                @error('website')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:fieldset>
                        </div>

                        <flux:fieldset>
                            <flux:label for="inscricao_estadual">Inscrição Estadual</flux:label>
                            <flux:input wire:model="inscricao_estadual" id="inscricao_estadual" placeholder="000.000.000.000" />
                            @error('inscricao_estadual')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:fieldset>
                    </div>
                @endif

                <!-- Tab: Endereco -->
                @if($activeTab === 'endereco')
                    <div class="space-y-4">
                        <flux:fieldset>
                            <flux:label for="endereco">Endereço</flux:label>
                            <flux:input wire:model="endereco" id="endereco" placeholder="Rua, número, bairro" />
                            @error('endereco')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:fieldset>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:fieldset>
                                <flux:label for="complemento">Complemento</flux:label>
                                <flux:input wire:model="complemento" id="complemento" placeholder="Sala, andar, etc." />
                                @error('complemento')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:fieldset>

                            <flux:fieldset>
                                <flux:label for="cep">CEP</flux:label>
                                <flux:input wire:model="cep" id="cep" placeholder="00000-000" />
                                @error('cep')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:fieldset>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:fieldset>
                                <flux:label for="bairro">Bairro</flux:label>
                                <flux:input wire:model="bairro" id="bairro" placeholder="Bairro" />
                                @error('bairro')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:fieldset>

                            <flux:fieldset>
                                <flux:label for="cidade">Cidade</flux:label>
                                <flux:input wire:model="cidade" id="cidade" placeholder="Cidade" />
                                @error('cidade')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:fieldset>
                        </div>

                        <flux:fieldset>
                            <flux:label for="estado">Estado (UF)</flux:label>
                            <flux:select wire:model="estado" id="estado" placeholder="Selecione o estado">
                                <option value="">Selecione</option>
                                <flux:select.option value="AC">Acre</flux:select.option>
                                <flux:select.option value="AL">Alagoas</flux:select.option>
                                <flux:select.option value="AP">Amapá</flux:select.option>
                                <flux:select.option value="AM">Amazonas</flux:select.option>
                                <flux:select.option value="BA">Bahia</flux:select.option>
                                <flux:select.option value="CE">Ceará</flux:select.option>
                                <flux:select.option value="DF">Distrito Federal</flux:select.option>
                                <flux:select.option value="ES">Espírito Santo</flux:select.option>
                                <flux:select.option value="GO">Goiás</flux:select.option>
                                <flux:select.option value="MA">Maranhão</flux:select.option>
                                <flux:select.option value="MT">Mato Grosso</flux:select.option>
                                <flux:select.option value="MS">Mato Grosso do Sul</flux:select.option>
                                <flux:select.option value="MG">Minas Gerais</flux:select.option>
                                <flux:select.option value="PA">Pará</flux:select.option>
                                <flux:select.option value="PB">Paraíba</flux:select.option>
                                <flux:select.option value="PR">Paraná</flux:select.option>
                                <flux:select.option value="PE">Pernambuco</flux:select.option>
                                <flux:select.option value="PI">Piauí</flux:select.option>
                                <flux:select.option value="RJ">Rio de Janeiro</flux:select.option>
                                <flux:select.option value="RN">Rio Grande do Norte</flux:select.option>
                                <flux:select.option value="RS">Rio Grande do Sul</flux:select.option>
                                <flux:select.option value="RO">Rondônia</flux:select.option>
                                <flux:select.option value="RR">Roraima</flux:select.option>
                                <flux:select.option value="SC">Santa Catarina</flux:select.option>
                                <flux:select.option value="SP">São Paulo</flux:select.option>
                                <flux:select.option value="SE">Sergipe</flux:select.option>
                                <flux:select.option value="TO">Tocantins</flux:select.option>
                            </flux:select>
                            @error('estado')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:fieldset>
                    </div>
                @endif

                <!-- Tab: Dados Bancarios -->
                @if($activeTab === 'dados_bancarios')
                    <div class="space-y-4">
                        <flux:fieldset>
                            <flux:label for="banco_nome">Banco</flux:label>
                            <flux:input wire:model="banco_nome" id="banco_nome" placeholder="Nome do banco" />
                            @error('banco_nome')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:fieldset>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:fieldset>
                                <flux:label for="banco_agencia">Agência</flux:label>
                                <flux:input wire:model="banco_agencia" id="banco_agencia" placeholder="0000" />
                                @error('banco_agencia')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:fieldset>

                            <flux:fieldset>
                                <flux:label for="banco_conta">Conta Corrente</flux:label>
                                <flux:input wire:model="banco_conta" id="banco_conta" placeholder="00000-0" />
                                @error('banco_conta')
                                    <flux:error>{{ $message }}</flux:error>
                                @enderror
                            </flux:fieldset>
                        </div>

                        <flux:fieldset>
                            <flux:label for="chave_pix">Chave PIX</flux:label>
                            <flux:input wire:model="chave_pix" id="chave_pix" placeholder="Email, CPF, CNPJ ou telefone" />
                            @error('chave_pix')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:fieldset>

                        <flux:fieldset>
                            <flux:label for="banco_titular_doc">CPF/CNPJ do Titular</flux:label>
                            <flux:input wire:model="banco_titular_doc" id="banco_titular_doc" placeholder="CPF ou CNPJ do titular da conta" />
                            @error('banco_titular_doc')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:fieldset>
                    </div>
                @endif

                <!-- Tab: Tags -->
                @if($activeTab === 'tags')
                    <div class="space-y-4">
                        <flux:fieldset>
                            <flux:label>Tipos de Entidade</flux:label>
                            <div class="mt-2 flex flex-wrap gap-3">
                                <flux:checkbox
                                    text="Artista"
                                    :checked="isTipoSelected('artista')"
                                    wire:click="toggleTipo('artista')"
                                />
                                <flux:checkbox
                                    text="Booker"
                                    :checked="isTipoSelected('booker')"
                                    wire:click="toggleTipo('booker')"
                                />
                                <flux:checkbox
                                    text="Cliente"
                                    :checked="isTipoSelected('cliente')"
                                    wire:click="toggleTipo('cliente')"
                                />
                                <flux:checkbox
                                    text="Fornecedor"
                                    :checked="isTipoSelected('fornecedor')"
                                    wire:click="toggleTipo('fornecedor')"
                                />
                            </div>
                            @error('tipo')
                                <flux:error>{{ $message }}</flux:error>
                            @enderror
                        </flux:fieldset>
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <flux:button wire:click="closeModal" variant="ghost">
                        Cancelar
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $isEditing ? 'Salvar Alterações' : 'Criar Entidade' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
