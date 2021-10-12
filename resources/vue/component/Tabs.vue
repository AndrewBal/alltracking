<template>
    <div>
        <div class="tabs">
            <ul>
                <li v-for="(tab, index) in tabs" :class="{ 'is-active': tab.isActive, 'hide-mobile': index >= tabsCount - 3  }">
                    <p :id="tab.id" @click="selectTab(tab)">{{ tab.name }}</p>
                </li>


                <div v-if="isMobile" class="btn-more">
                    <button type="button" >Еще</button>
                    <div class="custom-dropdown "  uk-dropdown="mode: click">
                        <li  v-for="(tab, index) in tabs" :class="{ 'is-active': tab.isActive, 'hide-mobile': index === 0  }">
                            <p :id="tab.id" @click="selectTab(tab)">{{ tab.name }}</p>
                        </li>
                    </div>
                </div>

            </ul>
        </div>

        <div class="tabs-details">
            <slot></slot>
        </div>
    </div>
</template>


<script>
import Tab from './Tab'
export default {
    name: "Tabs",
    data() {
        return {
            tabs: [],
            isMobile: false
        };
    },
    component: {
        Tab
    },
    beforeDestroy () {
        if (typeof window !== 'undefined') {
            window.removeEventListener('resize', this.onResize, { passive: true })
        }
    },
    mounted () {
        this.onResize()
        window.addEventListener('resize', this.onResize, { passive: true })
    },
    created() {
        this.tabs = this.$children;
    },
    computed: {
        tabsCount() {
            return this.tabs.length;
        },
    },
    methods: {
        selectTab(selectedTab) {
            this.tabs.forEach(tab => {
                tab.isActive = (tab.name == selectedTab.name);
            });
        },
        onResize () {
            this.isMobile = window.innerWidth < 768
        }
    }
}
</script>
