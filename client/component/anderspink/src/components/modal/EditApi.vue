<template>
  <Modal
    class="tui-workspaceModal"
    size="large"
    :dismissable="{
    esc: true,
    backdropClick: false,
    overlayClose: false,
  }"
  >
    <ModalContent
      :close-button="false"
      :title="$str('modal_title_edit_api', 'container_anderspink')"
      :title-id="$id('modal_title_new_api')"
    >
      <ApiForm
        :id="id"
        class="tui-workspaceModal__form"
        :submitting="submitting"
        :editing="true"
        :api-key="apikey"
        :team-name="teamname"
        @submit="editApi"
        @cancel="$emit('request-close')"
      />

    </ModalContent>
  </Modal>
</template>

<script>
import Modal from "tui/components/modal/Modal";
import ModalContent from "tui/components/modal/ModalContent";
import ApiForm from "anderspink/components/form/ApiForm";

import {notify} from 'tui/notifications';

import updateApi from 'container_anderspink/graphql/update_api';

export default {
  components: {
    Modal,
    ModalContent,
    ApiForm
  },

  props: {
    apikey: {
      type: String,
      required: true
    },

    teamname: {
      type: String,
      required: true
    },

    id: {
      type: [String, Number],
      required: true,
    }
  },

  data() {
    return {
      submitting: false
    }
  },

  methods: {
    async editApi({id, teamName, apiKey}) {
      if (this.submitting) {
        return;
      }

      this.submitting = true;

      try {
        const data = await this.$apollo.mutate({
          mutation: updateApi,
          refetchAll: false,
          variables: {
            id: id,
            name: teamName,
            key: apiKey
          },
        });
        this.$emit('edit-api', data.data.anderspink);
      } catch (e) {
        await notify({
          message: this.$str('error:edit_api', 'container_anderspink'),
          type: 'error',
        });
      } finally {
        this.submitting = false;
      }
    }
  }
}
</script>

<lang-strings>
{
"container_anderspink": [
"modal_title_edit_api",
"error:edit_api"
]
}
</lang-strings>

<style lang="scss">
.tui-workspaceModal {
  &__form {
    flex-grow: 1;
    height: 100%;
  }
}
</style>