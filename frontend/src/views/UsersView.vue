<template>
  <div class="page-container">
    <el-container>
      <el-main class="main-content">
        <div class="page-header">
          <h1 class="page-title">Gestão de Usuários</h1>
        </div>

        <!-- Filtros -->
        <el-card class="filters-card">
          <el-form :model="filters" inline>
            <el-form-item label="Tipo de Usuário">
              <el-select
                v-model="filters.userType"
                placeholder="Todos"
                clearable
                style="width: 150px"
                @change="applyFilters"
              >
                <el-option label="Admin" value="admin" />
                <el-option label="Básico" value="basic" />
              </el-select>
            </el-form-item>

            <el-form-item label="Email">
              <el-input
                v-model="filters.email"
                placeholder="Digite o email"
                clearable
                style="width: 250px"
                @input="onEmailInput"
              />
            </el-form-item>

            <el-form-item>
              <el-button :icon="Refresh" @click="clearFilters"> Limpar </el-button>
            </el-form-item>
          </el-form>
        </el-card>

        <el-card class="table-card">
          <el-table :data="userStore.users" v-loading="userStore.loading" style="width: 100%">
            <el-table-column prop="id" label="ID" width="80" />

            <el-table-column prop="name" label="Nome" min-width="200" />

            <el-table-column prop="email" label="Email" min-width="200" />

            <el-table-column prop="is_admin" label="Tipo" width="120">
              <template #default="scope">
                <el-tag :type="scope.row.is_admin ? 'success' : 'info'">
                  {{ scope.row.is_admin ? 'Admin' : 'Básico' }}
                </el-tag>
              </template>
            </el-table-column>

            <el-table-column prop="travel_requests_count" label="Pedidos" width="100" />

            <el-table-column prop="created_at" label="Cadastrado em" width="180">
              <template #default="scope">
                {{ formatDateTime(scope.row.created_at) }}
              </template>
            </el-table-column>

            <el-table-column label="Ações" width="120" fixed="right">
              <template #default="scope">
                <el-button
                  type="primary"
                  :icon="Edit"
                  circle
                  size="small"
                  @click="handleEdit(scope.row)"
                />
                <el-button
                  v-if="scope.row.id !== authStore.user?.id"
                  type="danger"
                  :icon="Delete"
                  circle
                  size="small"
                  @click="handleDelete(scope.row)"
                />
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-main>
    </el-container>

    <!-- Dialog de edição -->
    <el-dialog v-model="showEditDialog" title="Editar Usuário" width="500">
      <el-form ref="formRef" :model="editForm" :rules="rules" label-position="top">
        <el-form-item label="Nome" prop="name">
          <el-input v-model="editForm.name" />
        </el-form-item>

        <el-form-item label="Tipo de Usuário" prop="is_admin">
          <el-radio-group v-model="editForm.is_admin">
            <el-radio :label="false">Básico</el-radio>
            <el-radio :label="true">Admin</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="showEditDialog = false">Cancelar</el-button>
        <el-button type="primary" @click="handleUpdate" :loading="updating"> Salvar </el-button>
      </template>
    </el-dialog>

    <!-- Dialog de confirmação de exclusão -->
    <el-dialog v-model="showDeleteDialog" title="Confirmar Exclusão" width="400" align-center>
      <p>Tem certeza que deseja excluir este usuário?</p>
      <template #footer>
        <el-button @click="showDeleteDialog = false">Cancelar</el-button>
        <el-button type="danger" @click="confirmDelete" :loading="deleting"> Excluir </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useUserStore } from '@/stores/user'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { Edit, Delete, Refresh } from '@element-plus/icons-vue'

const userStore = useUserStore()
const authStore = useAuthStore()
const router = useRouter()

const showEditDialog = ref(false)
const showDeleteDialog = ref(false)
const updating = ref(false)
const deleting = ref(false)
const selectedUser = ref(null)
const formRef = ref(null)

const filters = reactive({
  userType: '',
  email: '',
})

const editForm = reactive({
  name: '',
  is_admin: false,
})

const rules = {
  name: [{ required: true, message: 'Nome é obrigatório', trigger: 'blur' }],
}

onMounted(() => {
  userStore.fetchUsers()
})

let emailTimeout = null

const applyFilters = () => {
  userStore.fetchUsers(filters)
}

const onEmailInput = () => {
  if (emailTimeout) {
    clearTimeout(emailTimeout)
  }

  emailTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const clearFilters = () => {
  filters.userType = ''
  filters.email = ''
  userStore.clearFilters()
  userStore.fetchUsers()
}

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('pt-BR')
}

const handleEdit = (user) => {
  selectedUser.value = user
  editForm.name = user.name
  editForm.is_admin = user.is_admin
  showEditDialog.value = true
}

const handleUpdate = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      updating.value = true
      const success = await userStore.updateUser(selectedUser.value.id, editForm)
      updating.value = false

      if (success) {
        showEditDialog.value = false

        if (selectedUser.value.id === authStore.user?.id) {
          await authStore.fetchUser()
          router.go(0)
        }
      }
    }
  })
}

const handleDelete = (user) => {
  selectedUser.value = user
  showDeleteDialog.value = true
}

const confirmDelete = async () => {
  deleting.value = true
  await userStore.deleteUser(selectedUser.value.id)
  deleting.value = false
  showDeleteDialog.value = false
}
</script>

<style scoped></style>
