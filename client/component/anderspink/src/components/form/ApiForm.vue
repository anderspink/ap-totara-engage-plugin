<template>
  <Form class="tui-workspaceForm" :vertical="true" input-width="full">
    <div class="tui-workspaceForm__container">
      <div class="tui-workspaceForm__inputs">
        <InputText
          v-if="id > 0"
          :id="id.toString()"
          v-model="id"
          class="hidden"
        />
        <FormRow
          v-slot="{ id }"
          :label="$str('team_name_label', 'container_anderspink')"
          class="tui-workspaceForm__formRow"
        >
          <InputText
            :id="id + '_name'"
            v-model="name"
            :required="true"
            :disabled="submitting"/>
        </FormRow>

        <FormRow
          :label="$str('api_key_label', 'container_anderspink')"
          class="tui-workspaceForm__formRow"
        >
          <InputText
            v-model="key"
            :required="true"
            :disabled="submitting || editing"/>
        </FormRow>

      </div>
    </div>

    <ButtonGroup class="tui-workspaceForm__buttonGroup">
      <LoadingButton
        :loading="submitting"
        :disabled="submitting || disableSubmit"
        :text="submitButtonText"
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
  </Form>
</template>

<script>
import Form from "tui/components/form/Form";
import InputText from "tui/components/form/InputText";
import FormRow from "tui/components/form/FormRow";
import ButtonGroup from "tui/components/buttons/ButtonGroup";
import Button from "tui/components/buttons/Button";
import LoadingButton from "totara_engage/components/buttons/LoadingButton";

export default {
  components: {
    LoadingButton,
    Button,
    ButtonGroup,
    FormRow,
    InputText,
    Form
  },

  props: {
    submitting: Boolean,
    editing: {
      type: Boolean,
      default: false
    },
    id: {
      type: [String, Number],
      default: 0
    },
    teamName: {
      type: String,
      default: '',
    },
    apiKey: {
      type: String,
      default: ''
    },
    submitButtonText: {
      type: String,
      default() {
        return this.$str('submit', 'container_anderspink');
      },
    }
  },

  data() {
    return {
      name: this.teamName,
      key: this.apiKey,
    }
  },

  computed: {
    disableSubmit() {
      return 0 === this.name.length;
    }
  },

  watch: {
    teamName(value) {
      if (value !== this.name) {
        this.name = value;
      }
    }
  },

  methods: {
    submit() {
      const params = {
        id: this.id,
        teamName: this.name,
        apiKey: this.key
      };

      this.$emit('submit', params);
    },
  }
}
</script>

<lang-strings>
 {
  "container_anderspink": [
    "team_name_label",
    "api_key_label",
    "submit"
  ],
  "core": [
    "cancel"
  ]
 }
</lang-strings>

<style lang="scss">
.tui-workspaceForm {
  display: flex;
  flex-direction: column;
  justify-content: space-between;

  &__container {
    display: flex;
    flex-direction: column-reverse;
    align-items: stretch;

    @media (min-width: $tui-screen-sm) {
      flex-direction: row;
    }
  }

  &__inputs {
    display: flex;
    flex-direction: column;

    @media (min-width: $tui-screen-sm) {
      width: 100%;
    }
  }

  &__formRow {
    // Overriding the margin
    &.tui-formRow {
      margin-bottom: 0;

      &:not(:first-child) {
        margin-top: var(--gap-8);
      }
    }
  }

  &__buttonGroup {
    display: flex;
    justify-content: flex-end;

    // This button group need to have the same width as the inputs div
    width: 100%;
    margin-top: var(--gap-8);

    &.tui-formBtnGroup {
      // Overriding the margin
      & > :not(:first-child) {
        margin: 0;
        margin-left: var(--gap-4);
      }
    }
  }
}
</style>
