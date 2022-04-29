<template>
  <div>
    <Loading v-if="$apollo.loading"/>
    <div v-show="showHelpText">
      {{ $str('table_no_workspaces', 'container_anderspink') }}
    </div>
    <Table
      v-show="showTable"
      class="tui-dataTable"
      :data="workspaces"
      :expandable-rows="false"
    >
      <template v-slot:header-row>
        <HeaderCell size="3">
          {{ $str('table_header_workspace_name', 'container_anderspink') }}
        </HeaderCell>

        <HeaderCell size="3">
          {{ $str('table_header_team_name', 'container_anderspink') }}
        </HeaderCell>

        <HeaderCell size="3">
          {{ $str('table_header_folder_name', 'container_anderspink') }}
        </HeaderCell>

        <HeaderCell size="3">
          {{ $str('table_header_actions', 'container_anderspink') }}
        </HeaderCell>
      </template>

      <template v-slot:row="{ row }">
        <Cell size="3" :column-header="$str('table_header_workspace_name', 'container_anderspink')">
          <span>{{ row.workspace_name }}</span>
        </Cell>

        <Cell size="3" :column-header="$str('table_header_team_name', 'container_anderspink')">
          <span>{{ row.team_name }}</span>
        </Cell>

        <Cell size="3" :column-header="$str('table_header_folder_name', 'container_anderspink')">
          <span>{{ row.type_name }}</span>
        </Cell>

        <Cell size="3" :column-header="$str('table_header_actions', 'container_anderspink')">
          <WorkspaceBriefingsActionButtons
            :delete-id="row.id"
            @delete-bridge="updateTableAfterDelete"
          />
        </Cell>
      </template>

    </Table>
  </div>
</template>

<script>
import Table from "tui/components/datatable/Table";
import HeaderCell from "tui/components/datatable/HeaderCell";
import Cell from "tui/components/datatable/Cell";
import Loading from "tui/components/icons/Loading";
import WorkspaceBriefingsActionButtons from "anderspink/components/button/WorkspaceBriefingsActionButtons";

// GraphQL queries
import getBridges from 'container_anderspink/graphql/get_workspace_briefing_bridges';

export default {
  components: {
    WorkspaceBriefingsActionButtons,
    HeaderCell,
    Table,
    Cell,
    Loading
  },

  data() {
    return {
      workspaces: [],
      showTable: false,
      showHelpText: false,
    }
  },

  apollo: {
    workspaces: {
      query: getBridges,
      update({bridges}) {
        if (bridges.length > 0) {
          this.showTable = true;
        } else {
          this.showHelpText = true;
        }

        return bridges;
      }
    }
  },

  methods: {
    updateTableAfterDelete(data) {
      let workspacesTmp = this.workspaces.filter(w => w.id !== parseInt(data.id));
      this.$delete(this.workspaces);

      this.workspaces = workspacesTmp;

      if (this.workspaces.length > 0) {
        this.showTable = true;
        this.showHelpText = false;
      } else {
        this.showTable = false;
        this.showHelpText = true;
      }
    },

    updateTableAfterAdd(data) {
      let workspaceTmp = [data, ...this.workspaces];
      this.$delete(this.workspaces);
      this.workspaces = workspaceTmp;

      if (this.workspaces.length > 0) {
        this.showTable = true;
        this.showHelpText = false;
      }
    }
  }
}
</script>
<lang-strings>
{
"container_anderspink": [
"table_header_team_name",
"table_header_workspace_name",
"table_header_actions",
"table_header_folder_name",
"table_no_workspaces"
]
}
</lang-strings>