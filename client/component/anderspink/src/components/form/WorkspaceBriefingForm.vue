<template>
  <div>
    <Form
      class="tui-workspaceForm"
      :vertical="true"
      input-width="full"
    >
      <FormRow
        v-slot="{ id }"
        :label="$str('workspace_name_label', 'container_anderspink')"
        class="tui-workspaceForm__formRow anderspink-workspace-form"
      >
        <label class="select-label">
          <Select
            v-model="workspaceid"
            :disabled="$apollo.loading"
            :name="'workspaces'"
            :options="workspaces"
            :large="false"
          />
        </label>
      </FormRow>
      <FormRow
        v-show="showApiElement"
        :label="$str('team_name_label', 'container_anderspink')"
        class="tui-workspaceForm__formRow anderspink-workspace-form"
      >
        <label class="select-label">
          <Select
            v-model="teamid"
            :disabled="$apollo.loading"
            :options="teams"
            :large="false"
            @input="fetchBriefings"
          />
        </label>
      </FormRow>
      <FormRow
        :label="$str('folder_name_label', 'container_anderspink')"
        class="tui-workspaceForm__formRow anderspink-workspace-form"
      >
        <label class="select-label">
          <Select
            v-model="typeid"
            :options="briefings"
            :disabled="briefingsDisable"
            :large="false"
          />
        </label>
      </FormRow>
    </Form>
    <ButtonGroup class="tui-workspaceForm__buttonGroup">
      <LoadingButton
        :loading="submitting"
        :disabled="submitting || disableSubmit"
        :text="$str('submit', 'container_anderspink')"
        :aria-label="$str('submit', 'container_anderspink')"
        :primary="true"
        type="submit"
        @click.prevent="submit"
      />

      <Button
        :text="$str('cancel', 'core')"
        :disabled="submitting"
        @click.prevent="$emit('cancel')"
      />

    </ButtonGroup>
  </div>
</template>

<script>
import Form from "tui/components/form/Form";
import FormRow from "tui/components/form/FormRow";
import Select from "tui/components/form/Select";
import ButtonGroup from "tui/components/buttons/ButtonGroup";
import Button from "tui/components/buttons/Button";
import LoadingButton from "totara_engage/components/buttons/LoadingButton";
import apolloClient from "tui/apollo_client";
import {notify} from "tui/notifications";

// GraphQL queries
import findWorkspaces from 'container_workspace/graphql/find_workspaces';
import getApis from 'container_anderspink/graphql/get_apis';
import getBriefings from 'container_anderspink/graphql/get_briefings';
import getBoards from 'container_anderspink/graphql/get_boards';


export default {
  components: {
    Select,
    Form,
    FormRow,
    ButtonGroup,
    Button,
    LoadingButton
  },

  props: {
    submitting: Boolean
  },
  data() {
    return {
      typeid: undefined,
      workspaceid: undefined,
      teamid: undefined,
      workspaces: [],
      teams: [],
      briefings: [],
      loading: false,
      briefingsDisable: true,
      showApiElement: false
    }
  },

  computed: {
    disableSubmit() {
      return this.typeid === undefined || this.workspaceid === undefined || this.teamid === undefined;
    }
  },

  apollo: {
    workspaces: {
      query: findWorkspaces,
      fetchPolicy: 'network-only',
      variables() {
        return {
          source: "ALL",
          sort: "RECENT",
          search_term: "",
          access: null,
        };
      },
      update({workspaces}) {
        this.workspaces = [];
        this.loading = false;

        const label = {
          label: this.$str('placeholder_bridge_workspace', 'container_anderspink'),
          id: 0,
          disabled: true,
        }

        const workspaceData = workspaces.map(w => {
          return {
            id: w.id,
            label: w.name
          }
        }).sort((a, b) => {
          if (a.label < b.label) return -1;
          if (a.label > b.label) return 1;
          return 0;
        });

        const data = [label, ...workspaceData];

        this.workspaceid = workspaceData.length === 1 ? workspaceData[0].id : 0;

        return data;
      }
    },

    teams: {
      query: getApis,
      fetchPolicy: 'network-only',
      update({apis}) {
        this.teams = [];
        this.loading = false;

        const label = {
          label: this.$str('placeholder_bridge_team', 'container_anderspink'),
          id: 0,
          disabled: true,
        }

        const apiData = apis.map(a => {
          return {
            id: a.id,
            label: a.team_name,
          };
        });

        this.teamid = apiData.length === 1 ? apiData[0].id : 0;
        this.showApiElement = apiData.length !== 1;

        const data = [label, ...apiData];

        if (this.teamid !== 0 && this.teamid !== undefined) {
          this.fetchBriefings();
        }

        return data;
      }
    }
  },
  methods: {
    async fetchBriefings() {
      if (this.loading === true) {
        return;
      }

      this.briefings = [];
      this.loading = true;

      try {
        const briefings = await apolloClient.query({
          query: getBriefings,
          variables: {
            teamid: this.teamid
          }
        });

        const boards = await apolloClient.query({
          query: getBoards,
          variables: {
            teamid: this.teamid
          }
        });

        if (briefings.data.briefings.length > 0) {
          const briefingData = {
            label: this.$str('briefing_select_category_label', 'container_anderspink'),
            options: briefings.data.briefings.map(b => {
              return {
                id: 'briefing_' + b.apiid,
                label: b.name,
                disabled: false,
              }
            }).sort((a, b) => {
              if (a.label < b.label) return -1;
              if (a.label > b.label) return 1;
              return 0;
            })
          };
          this.briefings.push(briefingData);
        }

        if (boards.data.boards.length > 0) {
          const boardsData = {
            label: this.$str('boards_select_category_label', 'container_anderspink'),
            options: boards.data.boards.map(b => {
              return {
                id: 'board_' + b.apiid,
                label: b.name,
                disabled: false,
              }
            }).sort((a, b) => {
              if (a.label < b.label) return -1;
              if (a.label > b.label) return 1;
              return 0;
            })
          }
          this.briefings.push(boardsData);
        }

        if (this.briefings.length > 0) {

          if (this.briefings[0].id !== 0) {
            this.briefings.unshift({
              label: this.$str('label_briefing_folder_to_workspace_select', 'container_anderspink'),
              id: 0,
              disabled: true
            });
            this.typeid = 0;
          }

          // Check if any have only 1 record
          if (this.briefings[0].length === 1 || this.briefings[1].length === 1) {
            // if first set of data only has 1 record but second set has 0 we set default
            if (this.briefings[0] === 1 && this.briefings[1].length === 0) {
              this.typeid = this.briefings[0].id
              // else if second set of data only has 1 record but first set has 0 we set default
            } else if (this.briefings[0] === 0 && this.briefings[1].length === 1) {
              this.typeid = this.briefings[1].id
            }
          }

        } else {

          const noData = {
            label: this.$str('boards_select_category_label_no_records', 'container_anderspink'),
          };

          this.briefings.push(noData);
          
        }

        this.briefingsDisable = false;
      } catch (e) {
        await notify({
          message: this.$str('error:fetch_briefing_folder', 'container_anderspink'),
          type: 'error',
        });
      } finally {
        this.loading = false;
      }
    },
    submit() {
      const params = {
        workspace: this.workspaceid,
        briefing: this.typeid,
        team: this.teamid
      };

      this.$emit('submit', params);
    }
  }
}
</script>

<lang-strings>
{
"container_anderspink": [
"team_name_label",
"workspace_name_label",
"folder_name_label",
"label_briefing_folder_to_workspace_select",
"error:fetch_briefing_folder",
"boards_select_category_label",
"briefing_select_category_label",
"submit",
"placeholder_bridge_workspace",
"placeholder_bridge_team",
"placeholder_bridge_type",
"boards_select_category_label_no_records"
],
"core": [
"cancel"
]
}
</lang-strings>


<style lang="scss">
.select-label {
  width: 100%;
}

.tui-workspaceForm__formRow.anderspink-workspace-form {
  margin-bottom: -15px;
}
</style>

