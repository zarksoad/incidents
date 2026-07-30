<template>
  <v-card class="glass-card">
    <template v-if="loading && incidents.length === 0">
      <BeautifulSkeleton type="table-row" v-for="n in 5" :key="n" />
    </template>
    <template v-else>
      <div class="table-responsive">
        <v-data-table-server
          :headers="headers"
          :items="incidents"
          :items-length="totalItems"
          :loading="loading"
          :items-per-page="itemsPerPage"
          :items-per-page-options="[
            { value: 10, title: '10' },
            { value: 25, title: '25' },
            { value: 50, title: '50' },
            { value: 100, title: '100' }
          ]"
          :page="page"
          :mobile-breakpoint="0"
          @update:page="$emit('update:page', $event)"
          @update:items-per-page="$emit('update:itemsPerPage', $event)"
          @update:sort-by="$emit('sort', $event)"
          hover
        >
          <template v-slot:item.priority="{ item }">
            <IncidentPriorityChip :priority="item.priority" />
          </template>

          <template v-slot:item.status="{ item }">
            <IncidentStatusChip :status="item.status" />
          </template>

          <template v-slot:item.assignee="{ item }">
            {{ item.assignee?.name || '—' }}
          </template>

          <template v-slot:item.due_date="{ item }">
            <span :class="{ 'text-error': item.is_overdue }">
              {{ item.due_date }}
              <v-icon v-if="item.is_overdue" size="16" color="error" icon="mdi-alert-circle" class="ml-1" />
            </span>
          </template>

          <template v-slot:item.actions="{ item }">
            <div class="d-flex flex-nowrap">
              <v-btn icon size="small" variant="text" :to="{ name: 'IncidentDetail', params: { id: item.id } }">
                <v-icon icon="mdi-eye" size="18" />
              </v-btn>
              <v-btn icon size="small" variant="text" :to="{ name: 'EditIncident', params: { id: item.id } }">
                <v-icon icon="mdi-pencil" size="18" />
              </v-btn>
              <v-btn
                v-if="isAdmin"
                icon
                size="small"
                variant="text"
                color="error"
                @click="$emit('delete', item)"
              >
                <v-icon icon="mdi-delete" size="18" />
              </v-btn>
            </div>
          </template>
        </v-data-table-server>
      </div>
    </template>
  </v-card>
</template>

<script setup>
import BeautifulSkeleton from '../BeautifulSkeleton.vue'
import IncidentStatusChip from '../atoms/IncidentStatusChip.vue'
import IncidentPriorityChip from '../atoms/IncidentPriorityChip.vue'

defineProps({
  incidents: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  totalItems: {
    type: Number,
    required: true
  },
  itemsPerPage: {
    type: Number,
    required: true
  },
  page: {
    type: Number,
    required: true
  },
  isAdmin: {
    type: Boolean,
    default: false
  }
})

defineEmits(['update:page', 'update:itemsPerPage', 'sort', 'delete'])

const headers = [
  { title: 'Título', key: 'title', sortable: true },
  { title: 'Prioridad', key: 'priority', sortable: true, width: '120px' },
  { title: 'Estado', key: 'status', sortable: true, width: '130px' },
  { title: 'Asignado', key: 'assignee', sortable: false, width: '150px' },
  { title: 'Vencimiento', key: 'due_date', sortable: true, width: '140px' },
  { title: 'Acciones', key: 'actions', sortable: false, width: '140px', align: 'center' },
]
</script>
