<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Chart Component</div>

                    <div class="card-body">
                        <div id = "container-for-charts">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapState } from 'vuex';
    import Highcharts from 'highcharts';

    export default {
        mounted() {
            window.axios
            .get("http://127.0.0.1:8000/api/users_list").then(
                
                data => {
                    
                    this.$store.commit('SET_LIST', data.data.cohort)
                }
            ).catch(error => {
                console.log(error);
            });
        },
        watch: {
            list() {
                this.dataSource()
            }
        },
        computed: mapState({
            list: state => state.list
        }),
        methods: {
            dataSource() {
                let week_1 = Object.values(this.list[30])
                let week_2 = Object.values(this.list[31])
                let week_3 = Object.values(this.list[32])
                let week_4 = Object.values(this.list[33])
                this.setUp({week_1, week_2, week_3, week_4})
            },
            setUp(obj) {
                const {week_1, week_2, week_3, week_4} = obj
                Highcharts.chart('container-for-charts', {
                    chart: {
                        type: 'spline',
                        scrollablePlotArea: {
                            minWidth: 600,
                            scrollPositionX: 1
                        }
                    },
                    title: {
                        text: 'WEEKLY RETENTION CURVES'
                    },
                     xAxis: {
                         title: {
                            text: 'Steps in the onboarding Process'
                        },
                        categories: [0, 20, 40, 50 ,60, 70, 90, 99, 100],
                        
                        
                    },
                    yAxis: {
                        title: {
                            text: '% number of users'
                        },
                        labels: {
                            formatter: function () {
                                return this.value;
                            }
                        }
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'middle'
                    },

                    series: [{
                        name: 'Week 1',
                        data: week_1,
                    }, {
                        name: 'Week 2',
                        data: week_2,
                    }, {
                        name: 'Week 3',
                        data: week_3,
                    }, {
                        name: 'Week 4',
                        data: week_4,
                    }],

                    responsive: {
                        rules: [{
                        chartOptions: {
                            legend: {
                            layout: 'horizontal',
                            align: 'center',
                            verticalAlign: 'bottom'
                            }
                        }
                        }]
                    }

                    });
            },
        }
    }
</script>
