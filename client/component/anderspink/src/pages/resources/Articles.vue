<template>
  <div class="tui-engageCreateArticle tui-form">
    <div v-show="stage === 0">
      <label class="select-label" v-show="showApiElement">
        {{$str('label_team_select', 'container_anderspink')}}
        <Select
          v-model="teamid"
          :disabled="$apollo.loading"
          :name="'teamname'"
          :options="apis"
          :large="false"
          @input="fetchBriefings"
        />
      </label>
      <label class="select-label">
        {{ $str('label_briefing_folder_select', 'container_anderspink') }}
        <Select
          v-model="typeid"
          :disabled="briefingsSelectDisable"
          :name="'briefings'"
          :options="briefings"
          :large="false"
          @input="fetchArticles"
        />
      </label>
      <slot name="cards">
        <section class="tui-contributionBaseContent__cards anderspink-card">
          <h4 v-show="!$apollo.loading && cardsVisible && cards.length >= 1" class="bold">
            {{ $str('label_article_select', 'container_anderspink') }}
          </h4>
          <h5 v-show="noCards" class="bold">
            {{ $str('no_cards', 'container_anderspink') }}
          </h5>
          <ComponentLoading v-show="loading"/>
          <Card v-for="(card, i) in cards"
                v-show="cards.length >= 1"
                :key="i"
          >
            <ContributeCardImage
              :instance-id="i"
              :img-source="card.image"
              :alt-text="card.name"
            />

            <ContributeCardContent
              :content="card"
              @addToWorkspace="nextFormPage"
            />
          </Card>
        </section>
      </slot>
      <div class="bottom-btn-group bottom text-center">
        <Button
          class="pull-left"
          v-show="!$apollo.loading && cardsVisible && cards.length >= 1"
          :class="{primary: true, small: true}"
          :text="$str('load_more_btn', 'container_anderspink')"
          @click="loadMoreArticles"
        />
        <Button
          class="pull-right"
          :class="{primary: true, small: true}"
          :text="$str('cancel', 'core')"
          @click="$emit('cancel')"
        />
      </div>
    </div>
    <div v-show="stage === 1">
      <CreateAnderspinkArticle
        :submitting="submitting"
        :card="selectedCard"
        :container="container"
        @back="stage = 0"
        @cancel="$emit('cancel')"
        @done="submit"
      />
    </div>
  </div>
</template>

<script>
import Select from "tui/components/form/Select";
import Card from "tui/components/card/Card";
import Button from "tui/components/buttons/Button";
import ContributeCardImage from "anderspink/components/card-content/ContributeCardImage";
import ContributeCardContent from "anderspink/components/card-content/ContributeCardContent";
import CreateAnderspinkArticle from "anderspink/components/CreateAnderspinkArticle";
import ComponentLoading from "tui/components/loading/ComponentLoading";

import {notify} from "tui/notifications";
import apolloClient from 'tui/apollo_client';

// GraphQL queries
import getApis from 'container_anderspink/graphql/get_apis';
import getBriefings from 'container_anderspink/graphql/get_briefings';
import getBoards from 'container_anderspink/graphql/get_boards';
import getArticles from 'engage_anderspink/graphql/get_api_articles';
import createArticle from 'engage_anderspink/graphql/create_article';

// Mixins
import ContainerMixin from 'totara_engage/mixins/container_mixin';


export default {
  components: {
    CreateAnderspinkArticle,
    ComponentLoading,
    Button,
    ContributeCardImage,
    ContributeCardContent,
    Card,
    Select,
  },

  mixins: [ContainerMixin],

  data() {
    return {
      teamid: undefined,
      typeid: undefined,
      page: 0,
      apis: [],
      briefings: [],
      cards: [],
      selectedCard: [],
      briefingsSelectDisable: true,
      cardsLoading: true,
      cardsVisible: false,
      loading: true,
      submitting: false,
      stage: 0,
      showApiElement: false,
      noCards: false
    }
  },

  apollo: {
    apis: {
      query: getApis,
      update({apis}) {
        this.loading = false;
        const data = apis.map(a => {
          return {
            id: a.id,
            label: a.team_name,
          };
        });

        if (data.length === 1) {
          this.teamid = data[0].id;
          this.fetchBriefings();
        } else {
          data.unshift({label: this.$str('label_team_select', 'container_anderspink'), id: 0, disabled: true});
          this.teamid = 0;
          this.showApiElement = true;
        }

        return data;
      }
    }
  },

  watch: {
    briefings(value) {
      this.briefings = value;
    }
  },

  methods: {
    fetchBriefings: async function () {
      this.loading = true;
      this.briefings = [];
      this.cards = [];
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
                id: 'briefings_' + b.apiid,
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
                id: 'boards_' + b.apiid,
                label: b.name,
                disabled: false,
              }
            }).sort((a, b) => {
              if (a.label < b.label) return -1;
              if (a.label > b.label) return 1;
              return 0;
            })
          };

          this.briefings.push(boardsData);
        }

        if (this.briefings[0].id !== 0) {
          this.briefings.unshift({
            label: this.$str('label_briefing_folder_select', 'container_anderspink'),
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
            await this.fetchArticles();
            // else if second set of data only has 1 record but first set has 0 we set default
          } else if (this.briefings[0] === 0 && this.briefings[1].length === 1) {
            this.typeid = this.briefings[1].id
            await this.fetchArticles();
          }
        }

        this.briefingsSelectDisable = false;
      } catch (e) {
        await notify({
          message: this.$str('error:fetch_briefing_folder', 'container_anderspink'),
          type: 'error',
        });
      } finally {
        this.loading = false;
      }
    },
    async fetchArticles() {
      this.cardsLoading = true;
      this.cardsVisible = false;
      this.noCards = false;
      this.loading = true;
      this.cards = [];
      this.page = 1;

      try {
        const [type, typeid] = this.typeid.split('_');

        const articles = await apolloClient.query({
          query: getArticles,
          variables: {
            teamid: this.teamid,
            typeid: typeid,
            type: type,
          }
        });

        this.cards = articles.data.articles.map(a => {
          return {
            id: 'articles_' + a.api_id,
            component: 'component',
            instanceId: a.api_id,
            name: a.title,
            summary: a.content,
            timeCreated: a.date_published,
            url: decodeURI(a.url),
            image: a.image,
            domain: a.domain,
            reading_time: a.reading_time,
          }
        });

        if (this.cards.length < 1) {
          this.noCards = true;
        }

        this.cardsVisible = true;
        this.cardsLoading = false;
        this.page = 1;
      } catch (e) {
        await notify({
          message: this.$str('error:fetch_articles', 'container_anderspink'),
          type: 'error',
        });
      } finally {
        this.loading = false;
      }
    },
    async loadMoreArticles() {
      this.loading = true;
      try {
        const [type, typeid] = this.typeid.split('_');
        this.page++;

        const articles = await apolloClient.query({
          query: getArticles,
          variables: {
            teamid: this.teamid,
            typeid: typeid,
            type: type,
            page: this.page
          }
        });

        if (articles.data.articles.length === 0) {
          await notify({
            message: this.$str('info:no_more_entries_to_load', 'container_anderspink'),
            type: 'info',
          });
          this.loading = false;
          return;
        }

        const newArticles = articles.data.articles.map(a => {
          return {
            id: 'articles_' + a.api_id,
            component: 'component',
            instanceId: a.api_id,
            name: a.title,
            summary: a.content,
            timeCreated: a.date_published,
            url: decodeURI(a.url),
            image: a.image,
            domain: a.domain,
            reading_time: a.reading_time
          }
        });

        this.cards = [...this.cards, ...newArticles];

      } catch (e) {
        await notify({
          message: this.$str('error:fetch_articles', 'container_anderspink'),
          type: 'error',
        });
      } finally {
        this.loading = false;
      }
    },
    nextFormPage(data) {
      this.stage = 1;
      this.selectedCard = this.cards.filter(card => card.instanceId === data.id).shift();
    },
    async submit({access, topics, timeView, shares, card}) {
      this.submitting = true;

      let articleData = {
        name: card.name,
        content: JSON.stringify({
          url: card.url,
          summary: card.summary,
          image: card.image,
          reading_time: card.reading_time,
          domain: card.domain,
          published: card.timeCreated,
          team: this.teamid,
          api: card.instanceId,
          briefing: this.typeid
        }),
        access: access,
        topics: topics.map(topic => topic.id),
        shares: shares,
      }

      if (timeView) {
        articleData.timeview = timeView;
      }

      this.$apollo
        .mutate({
          mutation: createArticle,
          refetchQueries: [
            'totara_engage_contribution_cards',
            'container_workspace_contribution_cards',
            'container_workspace_shared_cards',
          ],
          variables: articleData,
          update: (
            cache,
            {
              data: {
                article: {
                  resource: {id},
                },
              },
            }
          ) => {
            this.$emit('done', {resourceId: id});
          },
        })
        .then(({data: {article}}) => {
          if (article && this.showNotification && !this.container) {
            notify({
              message: this.$str('created', 'engage_article'),
              type: 'success',
            });
          }
          this.$emit('cancel');
        })

        .finally(() => {
          this.submitting = false;
        });

    }
  }
}
</script>

<lang-strings>
{
"container_anderspink": [
"label_team_select",
"label_briefing_folder_select",
"label_article_select",
"error:fetch_articles",
"error:fetch_briefing_folder",
"info:no_more_entries_to_load",
"load_more_btn",
"briefing_select_category_label",
"boards_select_category_label",
"no_cards"
],
"engage_article": [
"created"
],
"core": [
"cancel"
]
}
</lang-strings>

<style lang="scss">
.tui-engageCreateArticle {
  margin-bottom: 5px;
  padding-bottom: 5px;
}

.anderspink-card {
  min-height: 335px;
}

.select-label {
  width: 100%;
}
</style>
