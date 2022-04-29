<template>
  <div>
    <Loading v-if="$apollo.loading"/>
    <div v-show="showHelpText">
      {{ $str('table_no_teams', 'container_anderspink') }}
    </div>
    <Table
      v-show="showTable"
      class="tui-dataTable"
      :data="apis"
      :expandable-rows="false"
    >
      <template v-slot:header-row>
        <HeaderCell size="3">
          {{ $str('table_header_team_name', 'container_anderspink') }}
        </HeaderCell>

        <HeaderCell size="3">
          {{ $str('table_header_api_key', 'container_anderspink') }}
        </HeaderCell>

        <HeaderCell size="5">
          {{ $str('table_header_timecreated', 'container_anderspink') }}
        </HeaderCell>

        <HeaderCell size="5">
          {{ $str('table_header_timemodified', 'container_anderspink') }}
        </HeaderCell>

        <HeaderCell size="3">
          {{ $str('table_header_actions', 'container_anderspink') }}
        </HeaderCell>
      </template>

      <template v-slot:row="{ row }">
        <Cell size="3" :column-header="$str('table_header_team_name', 'container_anderspink')">
          <span>{{ row.team_name }}</span>
        </Cell>

        <Cell size="3" :column-header="$str('table_header_api_key', 'container_anderspink')">
          <span>{{ row.api_key }}</span>
        </Cell>

        <Cell size="5" :column-header="$str('table_header_timecreated', 'container_anderspink')">
          <span>{{ row.timecreated }}</span>
        </Cell>

        <Cell size="5" :column-header="$str('table_header_timemodified', 'container_anderspink')">
          <span>{{ row.timemodified }}</span>
        </Cell>

        <Cell size="3" :column-header="$str('table_header_actions', 'container_anderspink')">
          <ApiTableActionButtons
            :actions="row.id"
            :teamname="row.team_name"
            :apikey="row.api_key"
            @delete-api="updateTableAfterDelete"
            @edit-api="updateTableAfterEdit"
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
import ApiTableActionButtons from "anderspink/components/button/ApiTableActionButtons";
import Loading from "tui/components/icons/Loading";

// GraphQL queries
import getApis from 'container_anderspink/graphql/get_apis';

export default {
  components: {
    ApiTableActionButtons,
    Cell,
    HeaderCell,
    Table,
    Loading
  },

  apollo: {
    apis: {
      query: getApis,
      update({ apis }) {
        if (apis.length > 0) {
          this.showTable = true;
        } else {
          this.showHelpText = true;
        }

        return apis;
      }
    }
  },

  data() {
    return {
      apis: [],
      showTable: false,
      showHelpText: false,
    }
  },

  methods: {
    updateTableAfterAdd(data) {
      let apisTmp = [data, ...this.apis];
      this.$delete(this.apis);
      this.apis = apisTmp;

      if (this.apis.length > 0) {
        this.showTable = true;
        this.showHelpText = false;
      }
    },

    updateTableAfterDelete(data) {
      if (!data.result) {
        return;
      }

      let apisTmp = this.apis.filter(apis => apis.id !== parseInt(data.id));
      this.$delete(this.apis);

      this.apis = apisTmp;

      if (this.apis.length > 0) {
        this.showTable = true;
        this.showHelpText = false;
      } else {
        this.showTable = false;
        this.showHelpText = true;
      }
    },

    updateTableAfterEdit(data) {
      let apisTmp = this.apis.filter(apis => apis.id !== parseInt(data.id));
      apisTmp = [data, ...apisTmp];

      this.$delete(this.apis);
      this.apis = apisTmp;
    }
  }
}
</script>
<lang-strings>
{
"container_anderspink": [
"table_header_team_name",
"table_header_api_key",
"table_header_timecreated",
"table_header_timemodified",
"table_header_actions",
"table_no_teams"
]
}
</lang-strings>