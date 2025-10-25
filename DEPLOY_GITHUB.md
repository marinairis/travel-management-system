# 📤 Guia de Deploy no GitHub

Este guia fornece instruções detalhadas para fazer o upload do projeto Travel Management System para o GitHub.

## 📋 Pré-requisitos

- Git instalado no seu computador
- Conta no GitHub ([criar conta](https://github.com/signup))
- SSH configurado (recomendado) ou HTTPS

## 🔐 Configurar Git (Primeira vez)

Se é a primeira vez usando Git no seu computador:

```bash
# Configurar nome de usuário
git config --global user.name "Seu Nome"

# Configurar email (use o mesmo do GitHub)
git config --global user.email "seu.email@exemplo.com"

# Verificar configurações
git config --list
```

## 🔑 Configurar SSH (Recomendado)

### 1. Verificar se já possui chave SSH

```bash
ls -al ~/.ssh
```

Se ver arquivos como `id_rsa.pub` ou `id_ed25519.pub`, você já tem uma chave.

### 2. Gerar nova chave SSH (se necessário)

```bash
ssh-keygen -t ed25519 -C "seu.email@exemplo.com"
```

Pressione Enter para aceitar o local padrão e opcionalmente adicione uma senha.

### 3. Adicionar chave SSH ao agente

```bash
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_ed25519
```

### 4. Copiar chave SSH pública

```bash
cat ~/.ssh/id_ed25519.pub
```

Copie o conteúdo exibido.

### 5. Adicionar chave no GitHub

1. Acesse: https://github.com/settings/keys
2. Clique em "New SSH key"
3. Cole a chave copiada
4. Clique em "Add SSH key"

### 6. Testar conexão

```bash
ssh -T git@github.com
```

Você deve ver: "Hi username! You've successfully authenticated..."

## 📁 Preparar o Repositório Local

### 1. Navegar até a pasta do projeto

```bash
cd /home/marinairis/travel-management-system
```

### 2. Criar arquivo .gitignore

Crie um arquivo `.gitignore` na raiz do projeto para evitar enviar arquivos desnecessários:

```bash
# Backend
backend/vendor/
backend/node_modules/
backend/storage/*.key
backend/.env
backend/.env.backup
backend/.phpunit.result.cache
backend/Homestead.json
backend/Homestead.yaml
backend/npm-debug.log
backend/yarn-error.log
backend/.DS_Store
backend/public/hot
backend/public/storage
backend/storage/framework/cache/*
backend/storage/framework/sessions/*
backend/storage/framework/views/*
backend/storage/logs/*

# Frontend
frontend/node_modules/
frontend/dist/
frontend/.env
frontend/.env.local
frontend/.env.*.local
frontend/.DS_Store
frontend/npm-debug.log*
frontend/yarn-debug.log*
frontend/yarn-error.log*

# IDEs
.vscode/
.idea/
*.swp
*.swo
*~

# OS
.DS_Store
Thumbs.db
```

### 3. Inicializar repositório Git (se ainda não existe)

```bash
git init
```

### 4. Adicionar arquivos ao staging

```bash
# Adicionar todos os arquivos
git add .

# Ou adicionar seletivamente
git add README.md
git add DEPLOY_GITHUB.md
git add backend/
git add frontend/
```

### 5. Fazer o primeiro commit

```bash
git commit -m "Initial commit: Travel Management System"
```

## 🌐 Criar Repositório no GitHub

### Opção 1: Via Interface Web

1. Acesse: https://github.com/new
2. Preencha:
   - **Repository name**: `travel-management-system`
   - **Description**: "Sistema de gerenciamento de viagens com Laravel e Vue.js"
   - **Visibility**: Public ou Private (sua escolha)
   - **NÃO** marque "Initialize with README" (já temos um)
3. Clique em "Create repository"

### Opção 2: Via GitHub CLI

```bash
# Instalar GitHub CLI (se necessário)
# Ubuntu/Debian
curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | sudo dd of=/usr/share/keyrings/githubcli-archive-keyring.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main" | sudo tee /etc/apt/sources.list.d/github-cli.list > /dev/null
sudo apt update
sudo apt install gh

# Autenticar
gh auth login

# Criar repositório
gh repo create travel-management-system --public --source=. --remote=origin --push
```

## 🚀 Enviar Código para o GitHub

### 1. Adicionar remote origin

```bash
# Com SSH (recomendado)
git remote add origin git@github.com:SEU_USUARIO/travel-management-system.git

# Com HTTPS
git remote add origin https://github.com/SEU_USUARIO/travel-management-system.git
```

**Substitua `SEU_USUARIO` pelo seu nome de usuário do GitHub.**

### 2. Verificar remote

```bash
git remote -v
```

### 3. Fazer push do código

```bash
# Primeira vez (branch main)
git branch -M main
git push -u origin main

# Próximos pushes
git push
```

## 📝 Criar Arquivos de Exemplo (.env.example)

Antes de fazer push, é boa prática criar arquivos `.env.example` com valores de exemplo:

### Backend (.env.example)

```bash
cd backend
cat > .env.example << 'EOF'
APP_NAME="Travel Management System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

JWT_SECRET=
JWT_TTL=60

CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database

MAIL_MAILER=log
EOF
```

### Frontend (.env.example)

```bash
cd ../frontend
cat > .env.example << 'EOF'
VITE_API_URL=http://localhost:8000/api
VITE_APP_TITLE="Travel Management System"
EOF
```

### Adicionar e commitar

```bash
cd ..
git add backend/.env.example frontend/.env.example
git commit -m "Add environment example files"
git push
```

## 🔄 Workflow Diário com Git

### Fazer alterações no código

```bash
# Ver status das mudanças
git status

# Ver diferenças
git diff

# Adicionar arquivos modificados
git add .
# ou específicos
git add backend/app/Http/Controllers/UserController.php

# Fazer commit
git commit -m "Descrição clara da mudança"

# Enviar para o GitHub
git push
```

### Criar uma nova feature (branch)

```bash
# Criar e mudar para nova branch
git checkout -b feature/nome-da-feature

# Fazer alterações e commits
git add .
git commit -m "Implementa nova feature"

# Enviar branch para o GitHub
git push -u origin feature/nome-da-feature
```

### Mesclar branch (merge)

```bash
# Voltar para main
git checkout main

# Atualizar main
git pull origin main

# Mesclar feature
git merge feature/nome-da-feature

# Enviar merge
git push

# Deletar branch local (opcional)
git branch -d feature/nome-da-feature

# Deletar branch remota (opcional)
git push origin --delete feature/nome-da-feature
```

## 🔍 Comandos Git Úteis

### Visualização

```bash
# Ver histórico de commits
git log
git log --oneline --graph --all

# Ver branches
git branch -a

# Ver tags
git tag
```

### Desfazer mudanças

```bash
# Descartar mudanças em arquivo
git checkout -- arquivo.txt

# Desfazer último commit (mantém alterações)
git reset --soft HEAD~1

# Desfazer último commit (descarta alterações) ⚠️
git reset --hard HEAD~1

# Reverter commit específico
git revert <commit-hash>
```

### Atualizar repositório local

```bash
# Baixar mudanças
git fetch origin

# Baixar e mesclar
git pull origin main
```

## 🏷️ Criar Releases e Tags

### Criar tag

```bash
# Tag simples
git tag v1.0.0

# Tag anotada (recomendado)
git tag -a v1.0.0 -m "Versão 1.0.0 - Release inicial"

# Enviar tag
git push origin v1.0.0

# Enviar todas as tags
git push --tags
```

### Criar Release no GitHub

1. Acesse: `https://github.com/SEU_USUARIO/travel-management-system/releases`
2. Clique em "Create a new release"
3. Escolha a tag ou crie uma nova
4. Preencha título e descrição
5. Clique em "Publish release"

## 📚 Melhores Práticas

### Commits

- Use mensagens claras e descritivas
- Commits pequenos e frequentes
- Use o tempo presente: "Add feature" não "Added feature"
- Formato sugerido:

  ```
  tipo: descrição curta

  Descrição mais detalhada se necessário
  ```

  Tipos: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`

### Branches

- `main` ou `master` - código de produção
- `develop` - desenvolvimento ativo
- `feature/nome` - novas funcionalidades
- `fix/nome` - correções de bugs
- `hotfix/nome` - correções urgentes

### .gitignore

- Sempre ignore arquivos sensíveis (.env, chaves, senhas)
- Ignore dependências (node_modules, vendor)
- Ignore arquivos gerados (dist, build, cache)

## 🛠️ Solução de Problemas

### Erro: "Permission denied (publickey)"

```bash
# Verificar se SSH está configurado
ssh -T git@github.com

# Adicionar chave SSH novamente
ssh-add ~/.ssh/id_ed25519
```

### Erro: "remote origin already exists"

```bash
# Remover origin existente
git remote remove origin

# Adicionar novamente
git remote add origin git@github.com:SEU_USUARIO/travel-management-system.git
```

### Conflitos de merge

```bash
# Ver arquivos em conflito
git status

# Editar arquivos e resolver conflitos manualmente
# Procure por marcadores: <<<<<<<, =======, >>>>>>>

# Após resolver
git add .
git commit -m "Resolve merge conflicts"
git push
```

### Reverter arquivo para versão anterior

```bash
# Reverter para versão do último commit
git checkout HEAD -- arquivo.txt

# Reverter para commit específico
git checkout <commit-hash> -- arquivo.txt
git commit -m "Revert arquivo.txt to previous version"
```

## 🔒 Segurança

### ⚠️ NUNCA commite:

- Arquivos `.env` com credenciais reais
- Senhas, tokens, chaves de API
- Certificados SSL privados
- Dados sensíveis de usuários

### Se commitou acidentalmente:

```bash
# Remover do histórico (⚠️ reescreve histórico)
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch caminho/arquivo-sensivel" \
  --prune-empty --tag-name-filter cat -- --all

# Forçar push (⚠️ cuidado!)
git push origin --force --all
```

**Melhor prática**: Revogue/altere credenciais expostas imediatamente!

## 📖 Recursos Adicionais

- [Documentação Git](https://git-scm.com/doc)
- [GitHub Docs](https://docs.github.com)
- [Git Cheat Sheet](https://education.github.com/git-cheat-sheet-education.pdf)
- [Conventional Commits](https://www.conventionalcommits.org/)

## ✅ Checklist Final

- [ ] Git configurado com nome e email
- [ ] SSH configurado (ou HTTPS pronto)
- [ ] Arquivo .gitignore criado
- [ ] Arquivos .env.example criados
- [ ] README.md atualizado
- [ ] Repositório criado no GitHub
- [ ] Código enviado com `git push`
- [ ] Repositório visível no GitHub
- [ ] Clone e teste em outra máquina (opcional)

## 🎉 Pronto!

Seu projeto está agora no GitHub e pronto para ser compartilhado, colaborado e versionado!

Para manter atualizado:

```bash
git add .
git commit -m "Descrição das mudanças"
git push
```
