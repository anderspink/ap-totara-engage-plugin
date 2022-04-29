<template>
  <div class="tui-engageImageHeader">
    <div v-if="showCover" class="tui-engageImageHeader__actionContainer">
      <!-- This is where the icon should be -->
      <slot name="actions"/>
    </div>
    <div class="anderspink-img-background domain">
      <span>{{ domain }}</span>
    </div>
    <div class="anderspink-img-background published">
      <span>{{ published }}</span>
    </div>
    <slot name="image"/>
  </div>
</template>

<script>
export default {
  props: {
    showCover: {
      required: true,
      type: Boolean,
    },
    domain: String,
    published: String,
  },
};
</script>

<style lang="scss">
.anderspink-img-background {
  width: 100%;
  padding: 5px;
  font-size: 14px;
  background: var(--color-background);
  border-radius: var(--card-border-radius);

  &.domain {
    position: absolute;
    right: 0;
    bottom: -4px;
    width: 100%;
    overflow: hidden;
    text-align: left;
    text-overflow: ellipsis;
  }

  &.published {
    position: absolute;
    top: -3px;
    right: 0;
    width: auto;
  }
}

.tui-engageImageHeader {
  position: relative;
  width: 100%;
  height: 100%;
  border-top-left-radius: var(--card-border-radius);
  border-top-right-radius: var(--card-border-radius);

  &__actionContainer {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 999;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    border-top-left-radius: var(--card-border-radius);
    border-top-right-radius: var(--card-border-radius);
  }

  &__imageBox {
    &--covered,
    &--noCovered {
      width: 100%;
      height: 100%;
      border-top-left-radius: var(--card-border-radius);
      border-top-right-radius: var(--card-border-radius);
    }
  }
}
</style>
