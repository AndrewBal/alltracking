<template>
    <div v-show="isActive"><slot></slot></div>
</template>


<script>
export default {
    name: "Tab",
    props: {
        name: { required: true },
        selected: { default: false}
    },

    data() {

        return {
            isActive: false,
            mobile:  false
        };

    },

    computed: {

        id() {
            return '#' + this.name.toLowerCase().replace(/ /g, '-');
        }
    },

    mounted() {

        this.isActive = this.selected;

    }
}
</script>
