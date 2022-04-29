<template>
  <div class="tui-engageCreateArticle">
    <AccessForm
      component="engage_anderspink"
      item-id="0"
      :show-back="true"
      :submitting="submitting"
      :selected-access="defaultAccess"
      :private-disabled="true"
      :restricted-disabled="false"
      :enable-time-view="false"
      :container="container"
      @back="$emit('back')"
      @cancel="$emit('cancel')"
      @done="done"
    />
  </div>
</template>

<script>
import AccessForm from "totara_engage/components/form/AccessForm";

export default {
  components: {
    AccessForm
  },

  props: {
    submitting: {
      required: true,
      type: Boolean
    },
    card: {
      required: true,
      type: [Object, Array]
    },
    container: {
      required: true,
      type: [Object, Array]
    }
  },

  data() {
    return {
      defaultAccess: 'PUBLIC'
    }
  },

  methods: {
    done(data){
      data.card = this.card;
      this.$emit('done', data);
    }
  }
};
</script>
