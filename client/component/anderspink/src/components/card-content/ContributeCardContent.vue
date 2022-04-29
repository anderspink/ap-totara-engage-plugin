<template>
  <div class="content-area">
    <h4 class="header">{{ content.name }}</h4>
    <div class="content-meta">
      <span class="bold vertical-align pull-left">Published: {{ published }}</span>
      <span class="bold vertical-align pull-right">{{ readtime }}</span>
    </div>
    <p class="content-item-body">{{ content.summary }}</p>

    <div class="card-content-footer">
      <Button
        class="pull-right"
        :styleclass="{primary: true, small: true}"
        :text="$str('add_to_workspace_btn', 'container_anderspink')"
        :loading="false"
        @click="$emit('addToWorkspace', {id: content.instanceId})"
      />
    </div>
    <div class="pull-left card-content-meta-footer">
      <p class="bold">{{ content.domain }}</p>
    </div>
  </div>
</template>

<script>
import Button from "tui/components/buttons/Button";

export default {
  components: {Button},
  props: {
    content: Object
  },
  computed: {
    readtime: function () {
      const readingTime = Math.round(this.content.reading_time / 60);

      if (readingTime === 0) {
        return '';
      }

      return readingTime + ' ' + this.$str('min_read', 'container_anderspink');
    },
    published: function () {
      const epochs = [
        ['year', 31536000],
        ['month', 2592000],
        ['day', 86400],
        ['hour', 3600],
        ['minute', 60],
        ['second', 1]
      ];

      const getDuration = (timeAgoInSeconds) => {
        for (let [name, seconds] of epochs) {
          const interval = Math.floor(timeAgoInSeconds / seconds);
          if (interval >= 1) {
            return {
              interval: interval,
              epoch: name
            };
          }
        }
      };

      const timeAgo = (date) => {
        const timeAgoInSeconds = Math.floor((new Date() - new Date(date)) / 1000);
        const {interval, epoch} = getDuration(timeAgoInSeconds);
        const suffix = interval === 1 ? '' : 's';
        return `${interval} ${epoch}${suffix} ago`;
      };

      return timeAgo(this.content.timeCreated);
    }
  }
}
</script>

<lang-strings>
{
"container_anderspink": [
"add_to_workspace_btn",
"min_read"
]
}
</lang-strings>

<style lang="scss">
.header {
  font-weight: bold;
  @media screen and (max-width: $tui-screen-xs) {
    font-size: 16px;
  }
}

.content-meta {
  height: 25px;
  padding-right: 10px;

  @media screen and (max-width: $tui-screen-xs) {
    font-size: 12px;
  }
}

.divider {
  width: 100%;
  margin-top: 10px;
  margin-bottom: 10px;
}

.content-item-body {
  width: 600px;
  height: 75px;
  margin-right: 10px;

  @media screen and (max-width: $tui-screen-xs) {
    margin-bottom: 20px;
    font-size: 12px;
  }
}

.card-content-footer {
  position: relative;
  bottom: 10px;
  padding-right: 10px;

  @media screen and (max-width: $tui-screen-xs) {
    bottom: 5px;

    button {
      width: 125px;
      height: 25px;
      font-size: 11px;
      white-space: nowrap;
    }
  }

  @media screen and(max-width: $tui-screen-xs) {
    .card-content-meta-footer {
      font-size: 12px;
    }
  }
}
</style>