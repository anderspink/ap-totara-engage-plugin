<template>
  <div>
    <ModalPresenter :open="openModal" @request-close="openModal = false">
      <EditApiModal
        :id="actions"
        :apikey="apikey"
        :teamname="teamname"
        @edit-api="editEntry"
      />
    </ModalPresenter>
    <div class="action-buttons">
      <Button
        :id="editAction"
        class="tui-formBtn--prim tui-formBtn--small"
        :text="$str('action_edit_btn', 'container_anderspink')"
        :disabled="disabled"
        @click.prevent="openModal = true"
      />
      <LoadingButton
        :id="deleteAction"
        class="tui-formBtn--prim tui-formBtn--small"
        :loading="submittingDelete"
        :text="$str('action_delete_btn', 'container_anderspink')"
        @click="deleteEntry"
      />
    </div>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ModalPresenter from "tui/components/modal/ModalPresenter";
import EditApiModal from "anderspink/components/modal/EditApi";

// GraphQL
import deleteApi from 'container_anderspink/graphql/delete_api';
import {notify} from "tui/notifications";
import LoadingButton from "totara_engage/components/buttons/LoadingButton";

export default {
  components: {
    LoadingButton,
    Button,
    ModalPresenter,
    EditApiModal
  },

  props: {
    id: Number,
    actions: [String, Number],
    teamname: String,
    apikey: String,
    disabled: Boolean,
  },

  data() {
    return {
      editAction: this.actions + '_edit',
      deleteAction: this.actions + '_delete',
      submittingDelete: false,
      submittingEdit: false,
      openModal: false,
    }
  },

  methods: {
    async deleteEntry() {
      this.submitting_delete = true;

      try {
        const data = await this.$apollo.mutate({
          mutation: deleteApi,
          refetchAll: false,
          variables: {
            id: this.actions
          },
        });

        const emitData = {
          id: this.actions,
          result: data.data.container_anderspink_delete_api
        };

        this.$emit('delete-api', emitData);
      } catch (e) {
        await notify({
          message: this.$str('error:delete_api', 'container_anderspink'),
          type: 'error',
        });
      } finally {
        this.submitting_delete = false;
      }
    },

    editEntry(anderspink_api) {
      this.openModal = false;
      this.$emit('edit-api', anderspink_api);
    },
  }
}
</script>

<lang-strings>
{
"container_anderspink": [
"action_edit_btn",
"action_delete_btn",
"error:delete_api"
]
}
</lang-strings>

<style lang="scss">
.action-buttons {
  button {
    width: 80px;
    margin-bottom: 5px;
    white-space: nowrap;
  }
}
</style>