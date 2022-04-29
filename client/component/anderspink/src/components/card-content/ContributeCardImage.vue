<template>
  <div class="image-container">
    <img class="tui-responsiveImage anderspink-image" :src="imgSource" :alt="altText" @load="$emit('img-loaded')"/>
  </div>
</template>

<script>
export default {
  props: {
    imgSource: {
      type: String,
      required: true
    },
    altText: {
      type: String,
      required: true
    },
    instanceId: {
      type: Number,
      required: true
    }
  }
}
</script>

<style lang="scss">
 .tui-card {
   .image-container {
     width: 100%;
     max-width: 20%;
     padding: 5px;

     img {
       width: 80px;
     }

     @media screen and (max-width: $tui-screen-xs) {
       .anderspink-image {
         display: none;
       }
     }
   }
 }
</style>
