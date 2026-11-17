import * as echarts from 'echarts/core';
import {
    BarChart,
    LineChart,
    PieChart,
    FunnelChart,
    HeatmapChart,
    ScatterChart,
    GaugeChart,
} from 'echarts/charts';
import {
    GridComponent,
    TooltipComponent,
    LegendComponent,
    VisualMapComponent,
    MarkLineComponent,
    MarkPointComponent,
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([
    BarChart,
    LineChart,
    PieChart,
    FunnelChart,
    HeatmapChart,
    ScatterChart,
    GaugeChart,
    GridComponent,
    TooltipComponent,
    LegendComponent,
    VisualMapComponent,
    MarkLineComponent,
    MarkPointComponent,
    CanvasRenderer,
]);

const FONT_SANS = "'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif";
const FONT_MONO = "'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, monospace";

const COLORS = {
    ink: '#0f172a',
    slateText: '#475569',
    mutedAxis: '#64748b',
    gridLine: '#f1f5f9',
    axisBorder: '#e2e8f0',
    blue: '#2563eb',
    blueDark: '#1d4ed8',
    indigo: '#4f46e5',
    emerald: '#059669',
    emeraldDark: '#047857',
    amber: '#f59e0b',
    red: '#dc2626',
};

function formatRupiahFull(value) {
    return 'Rp ' + Math.round(Number(value) || 0).toLocaleString('id-ID');
}

function formatRupiahCompact(value) {
    const num = Number(value) || 0;
    if (num >= 1_000_000_000) {
        const val = (num / 1_000_000_000).toFixed(1).replace(/\.0$/, '');
        return 'Rp ' + val + ' M';
    }
    if (num >= 1_000_000) {
        const val = (num / 1_000_000).toFixed(1).replace(/\.0$/, '');
        return 'Rp ' + val + ' Jt';
    }
    if (num >= 1_000) {
        return 'Rp ' + Math.round(num / 1_000) + ' Rb';
    }
    return 'Rp ' + num;
}

function tooltipDefaults(extra = {}) {
    return {
        backgroundColor: 'rgba(15, 23, 42, 0.96)',
        borderColor: '#1e293b',
        borderWidth: 1,
        padding: [10, 14],
        textStyle: {
            color: '#f8fafc',
            fontFamily: FONT_SANS,
            fontSize: 12,
        },
        extraCssText: 'border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);',
        ...extra,
    };
}

function buildOwnerComboOption(cfg) {
    const labels = cfg.labels || [];
    const parts = (cfg.parts || []).map(Number);
    const labor = (cfg.labor || []).map(Number);
    const consumable = (cfg.consumable || []).map(Number);
    const counts = (cfg.counts || []).map(Number);
    const totals = (cfg.revenues || []).map(Number);

    const seriesMeta = [
        { name: 'Suku Cadang', values: parts, color: COLORS.ink },
        { name: 'Jasa Mekanik', values: labor, color: COLORS.blue },
        { name: 'Bahan & Pelumas', values: consumable, color: COLORS.amber },
    ];

    const stackedSeries = seriesMeta.map((seriesItem, sIdx) => ({
        name: seriesItem.name,
        type: 'bar',
        stack: 'revenue',
        barMaxWidth: 34,
        color: seriesItem.color,
        itemStyle: { color: seriesItem.color },
        emphasis: { focus: 'series' },
        data: seriesItem.values.map((val, mIdx) => {
            const hasAbove = seriesMeta
                .slice(sIdx + 1)
                .some((above) => (above.values[mIdx] || 0) > 0);
            const radius = !hasAbove && val > 0 ? [8, 8, 0, 0] : [0, 0, 0, 0];
            return {
                value: val,
                itemStyle: {
                    color: seriesItem.color,
                    borderRadius: radius,
                },
            };
        }),
    }));

    return {
        textStyle: { fontFamily: FONT_SANS },
        tooltip: tooltipDefaults({
            trigger: 'axis',
            axisPointer: {
                type: 'shadow',
                shadowStyle: { color: 'rgba(148, 163, 184, 0.09)' },
            },
            formatter(params) {
                if (!params?.length) {
                    return '';
                }
                const idx = params[0].dataIndex;
                const totalRev = totals[idx] ?? 0;
                const rows = params
                    .map((item) => {
                        const valText =
                            item.seriesType === 'line'
                                ? `${item.value} unit`
                                : formatRupiahFull(item.value);
                        const color = item.seriesType === 'line' ? COLORS.emerald : item.color;
                        return `<div style="display:flex;align-items:center;justify-content:space-between;gap:18px;margin-top:4px;">
                            <span style="display:inline-flex;align-items:center;gap:6px;color:#cbd5e1;">
                                <span style="width:8px;height:8px;border-radius:99px;background:${color};display:inline-block;"></span>
                                ${item.seriesName}
                            </span>
                            <span style="font-family:${FONT_MONO};font-weight:700;color:#ffffff;">${valText}</span>
                        </div>`;
                    })
                    .join('');
                return `<div style="font-weight:700;color:#ffffff;margin-bottom:6px;border-bottom:1px solid rgba(148,163,184,0.2);padding-bottom:4px;">
                        ${params[0].axisValue} — ${formatRupiahFull(totalRev)}
                    </div>${rows}`;
            },
        }),
        legend: {
            top: 0,
            left: 0,
            icon: 'circle',
            itemWidth: 9,
            itemHeight: 9,
            itemGap: 16,
            textStyle: {
                color: COLORS.slateText,
                fontSize: 12,
                fontWeight: 600,
                fontFamily: FONT_SANS,
            },
        },
        grid: { top: 44, left: 6, right: 8, bottom: 6, containLabel: true },
        xAxis: {
            type: 'category',
            data: labels,
            axisLine: { lineStyle: { color: COLORS.axisBorder } },
            axisTick: { show: false },
            axisLabel: { color: COLORS.mutedAxis, fontSize: 11, fontWeight: 600, margin: 10 },
        },
        yAxis: [
            {
                type: 'value',
                min: 0,
                splitNumber: 4,
                splitLine: { lineStyle: { color: COLORS.gridLine, type: 'dashed' } },
                axisLabel: {
                    color: COLORS.mutedAxis,
                    fontSize: 11,
                    fontFamily: FONT_MONO,
                    formatter: (val) => formatRupiahCompact(val),
                },
            },
            {
                type: 'value',
                min: 0,
                minInterval: 1,
                splitLine: { show: false },
                axisLabel: {
                    color: COLORS.emeraldDark,
                    fontSize: 11,
                    fontWeight: 600,
                    fontFamily: FONT_MONO,
                    formatter: '{value} srv',
                },
            },
        ],
        series: [
            ...stackedSeries,
            {
                name: 'Unit Selesai',
                type: 'line',
                yAxisIndex: 1,
                data: counts,
                smooth: 0.35,
                symbol: 'circle',
                symbolSize: 8,
                lineStyle: { width: 3, color: COLORS.emerald },
                itemStyle: {
                    color: '#ffffff',
                    borderColor: COLORS.emerald,
                    borderWidth: 2.5,
                },
                labelLayout: { hideOverlap: true },
                label: {
                    show: true,
                    position: 'top',
                    distance: 6,
                    formatter: '{c} srv',
                    fontFamily: FONT_MONO,
                    fontSize: 10,
                    fontWeight: 700,
                    color: COLORS.emeraldDark,
                    backgroundColor: 'rgba(255, 255, 255, 0.85)',
                    borderRadius: 4,
                    padding: [2, 4],
                },
            },
        ],
    };
}

function buildOwnerRingOption(cfg) {
    const items = (cfg.items || []).map((item) => ({
        name: item.label,
        value: Number(item.value) || 0,
        itemStyle: { color: item.color },
    }));
    const hasData = items.some((i) => i.value > 0);

    return {
        textStyle: { fontFamily: FONT_SANS },
        tooltip: hasData
            ? tooltipDefaults({
                  trigger: 'item',
                  formatter(params) {
                      return `<div style="font-weight:700;">${params.name}</div>
                        <div style="font-family:${FONT_MONO};margin-top:3px;">${formatRupiahFull(params.value)} (${params.percent}%)</div>`;
                  },
              })
            : { show: false },
        series: [
            {
                type: 'pie',
                radius: ['68%', '88%'],
                center: ['50%', '50%'],
                padAngle: hasData ? 3 : 0,
                avoidLabelOverlap: false,
                itemStyle: {
                    borderRadius: hasData ? 6 : 0,
                },
                label: { show: false },
                labelLine: { show: false },
                emphasis: hasData
                    ? { scale: true, scaleSize: 4 }
                    : { disabled: true },
                data: hasData
                    ? items.filter((i) => i.value > 0)
                    : [{ name: 'Belum Ada Transaksi', value: 1, itemStyle: { color: '#e2e8f0' } }],
            },
        ],
    };
}

function buildSuperAdminAreaOption(cfg) {
    const labels = cfg.labels || [];
    const counts = (cfg.counts || []).map(Number);
    const avg = Number(cfg.avg) || 0;
    const maxVal = counts.length ? Math.max(...counts) : 0;

    return {
        textStyle: { fontFamily: FONT_SANS },
        tooltip: tooltipDefaults({
            trigger: 'axis',
            formatter(params) {
                const p = params?.[0];
                if (!p) {
                    return '';
                }
                return `<div style="font-weight:700;">${p.axisValue}</div>
                    <div style="margin-top:4px;font-family:${FONT_MONO};color:#93c5fd;">
                        ${p.value} Booking Servis
                    </div>`;
            },
        }),
        grid: { top: 28, left: 8, right: 16, bottom: 6, containLabel: true },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: labels,
            axisLine: { lineStyle: { color: COLORS.axisBorder } },
            axisTick: { show: false },
            axisLabel: {
                color: COLORS.mutedAxis,
                fontSize: 11,
                hideOverlap: true,
                margin: 10,
            },
        },
        yAxis: {
            type: 'value',
            min: 0,
            minInterval: 1,
            splitLine: { lineStyle: { color: COLORS.gridLine, type: 'dashed' } },
            axisLabel: {
                color: COLORS.mutedAxis,
                fontSize: 11,
                fontFamily: FONT_MONO,
            },
        },
        series: [
            {
                name: 'Booking Harian',
                type: 'line',
                smooth: 0.4,
                showSymbol: false,
                symbol: 'circle',
                symbolSize: 8,
                data: counts,
                lineStyle: { width: 3, color: COLORS.blue },
                itemStyle: {
                    color: '#ffffff',
                    borderColor: COLORS.blue,
                    borderWidth: 2.5,
                },
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(37, 99, 235, 0.25)' },
                        { offset: 1, color: 'rgba(37, 99, 235, 0.01)' },
                    ]),
                },
                markLine: avg > 0
                    ? {
                          silent: true,
                          symbol: 'none',
                          lineStyle: { color: COLORS.amber, type: 'dashed', width: 1.5 },
                          label: {
                              position: 'insideEndTop',
                              formatter: `Rata-rata ${avg}/hr`,
                              color: '#b45309',
                              backgroundColor: '#fef3c7',
                              borderRadius: 6,
                              padding: [3, 7],
                              fontFamily: FONT_MONO,
                              fontSize: 10,
                              fontWeight: 700,
                          },
                          data: [{ yAxis: avg }],
                      }
                    : undefined,
                markPoint: maxVal > 0
                    ? {
                          symbolSize: 38,
                          itemStyle: { color: COLORS.blueDark },
                          label: {
                              color: '#ffffff',
                              fontFamily: FONT_MONO,
                              fontSize: 10,
                              fontWeight: 700,
                          },
                          data: [{ type: 'max', name: 'Puncak' }],
                      }
                    : undefined,
            },
        ],
    };
}

function buildSuperAdminHeatmapOption(cfg) {
    const days = cfg.days || [];
    const slots = cfg.slots || [];
    const maxCount = Math.max(1, Number(cfg.maxCount) || 1);
    const threshold = maxCount * 0.6;

    const points = (cfg.points || []).map(([dayIdx, slotIdx, val]) => ({
        value: [dayIdx, slotIdx, val],
        label: {
            color: val >= threshold && val > 0 ? '#ffffff' : COLORS.ink,
        },
    }));

    return {
        textStyle: { fontFamily: FONT_SANS },
        tooltip: tooltipDefaults({
            position: 'top',
            formatter(params) {
                const [dIdx, sIdx, val] = params.value;
                return `<div style="font-weight:700;">${days[dIdx] ?? ''} · Jam ${slots[sIdx] ?? ''}</div>
                    <div style="margin-top:3px;font-family:${FONT_MONO};color:#93c5fd;">
                        ${val} Unit Terjadwal
                    </div>`;
            },
        }),
        grid: { top: 8, left: 48, right: 6, bottom: 34 },
        xAxis: {
            type: 'category',
            data: days,
            axisLine: { show: false },
            axisTick: { show: false },
            splitArea: { show: false },
            axisLabel: {
                color: COLORS.mutedAxis,
                fontSize: 10,
                fontWeight: 600,
                lineHeight: 13,
                interval: 0,
                formatter: (val) => String(val).replace(' ', '\n'),
            },
        },
        yAxis: {
            type: 'category',
            data: slots,
            inverse: true,
            axisLine: { show: false },
            axisTick: { show: false },
            axisLabel: { color: COLORS.slateText, fontSize: 11, fontFamily: FONT_MONO, fontWeight: 600 },
        },
        visualMap: {
            show: false,
            min: 0,
            max: maxCount,
            inRange: {
                color: ['#f1f5f9', '#dbeafe', '#93c5fd', '#2563eb', '#1e3a8a'],
            },
        },
        series: [
            {
                name: 'Slot Bengkel',
                type: 'heatmap',
                data: points,
                itemStyle: {
                    borderRadius: 6,
                    borderColor: '#ffffff',
                    borderWidth: 3,
                },
                label: {
                    show: true,
                    fontFamily: FONT_MONO,
                    fontSize: 11,
                    fontWeight: 700,
                    formatter: (p) => (p.value[2] > 0 ? String(p.value[2]) : ''),
                },
                emphasis: {
                    itemStyle: {
                        borderColor: COLORS.ink,
                        borderWidth: 1.5,
                    },
                },
            },
        ],
    };
}

function buildSalesFunnelOption(cfg, mode = 'funnel') {
    if (mode === 'trend') {
        const monthly = cfg.monthly || [];
        const labels = monthly.map((m) => m.month_label);
        const totals = monthly.map((m) => Number(m.total_leads) || 0);
        const completed = monthly.map((m) => Number(m.completed_leads) || 0);

        return {
            textStyle: { fontFamily: FONT_SANS },
            tooltip: tooltipDefaults({ trigger: 'axis', axisPointer: { type: 'shadow' } }),
            legend: {
                top: 0,
                left: 0,
                icon: 'circle',
                itemWidth: 9,
                itemHeight: 9,
                textStyle: { color: COLORS.slateText, fontSize: 12, fontWeight: 600 },
            },
            grid: { top: 40, left: 6, right: 10, bottom: 6, containLabel: true },
            xAxis: {
                type: 'category',
                data: labels,
                axisLine: { lineStyle: { color: COLORS.axisBorder } },
                axisTick: { show: false },
                axisLabel: { color: COLORS.mutedAxis, fontSize: 11, fontWeight: 600 },
            },
            yAxis: {
                type: 'value',
                min: 0,
                minInterval: 1,
                splitLine: { lineStyle: { color: COLORS.gridLine, type: 'dashed' } },
                axisLabel: { color: COLORS.mutedAxis, fontSize: 11, fontFamily: FONT_MONO },
            },
            series: [
                {
                    name: 'Total Pengajuan',
                    type: 'bar',
                    barMaxWidth: 26,
                    data: totals,
                    itemStyle: { color: COLORS.blue, borderRadius: [6, 6, 0, 0] },
                },
                {
                    name: 'Selesai Dicoba',
                    type: 'bar',
                    barMaxWidth: 26,
                    data: completed,
                    itemStyle: { color: COLORS.emerald, borderRadius: [6, 6, 0, 0] },
                },
            ],
        };
    }

    const stages = cfg.stages || [];
    const maxVal = Math.max(1, ...stages.map((s) => Number(s.value) || 0));
    const stageColors = [COLORS.blue, COLORS.indigo, COLORS.emerald];

    return {
        textStyle: { fontFamily: FONT_SANS },
        tooltip: tooltipDefaults({
            trigger: 'item',
            formatter(params) {
                const rate = params.data?.rate ?? 0;
                return `<div style="font-weight:700;">${params.name}</div>
                    <div style="margin-top:4px;font-family:${FONT_MONO};color:#93c5fd;">
                        ${params.value} Lead (${rate}% dari pengajuan)
                    </div>`;
            },
        }),
        series: [
            {
                name: 'Konversi Lead',
                type: 'funnel',
                left: '4%',
                right: '36%',
                top: 14,
                bottom: 14,
                min: 0,
                max: maxVal,
                minSize: '34%',
                maxSize: '100%',
                sort: 'none',
                gap: 8,
                label: {
                    show: true,
                    position: 'right',
                    formatter(params) {
                        const rate = params.data?.rate ?? 0;
                        return `{name|${params.name}}\n{val|${params.value} lead}  {pct|${rate}%}`;
                    },
                    rich: {
                        name: {
                            color: COLORS.ink,
                            fontSize: 12,
                            fontWeight: 700,
                            lineHeight: 18,
                            fontFamily: FONT_SANS,
                        },
                        val: {
                            color: COLORS.slateText,
                            fontSize: 11,
                            fontWeight: 700,
                            fontFamily: FONT_MONO,
                        },
                        pct: {
                            color: COLORS.blueDark,
                            backgroundColor: '#eff6ff',
                            borderRadius: 4,
                            padding: [2, 6],
                            fontSize: 10,
                            fontWeight: 700,
                            fontFamily: FONT_MONO,
                        },
                    },
                },
                labelLine: {
                    length: 14,
                    lineStyle: { width: 1.5, color: '#cbd5e1', type: 'dashed' },
                },
                itemStyle: {
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    borderRadius: 8,
                },
                data: stages.map((stage, idx) => ({
                    name: stage.name,
                    value: Number(stage.value) || 0,
                    rate: stage.rate ?? 0,
                    itemStyle: { color: stageColors[idx % stageColors.length] },
                })),
            },
        ],
    };
}

function buildSalesLollipopOption(cfg) {
    const items = [...(cfg.items || [])].reverse();
    const labels = items.map((i) => i.label);
    const values = items.map((i) => Number(i.value) || 0);
    const palette = [COLORS.blue, COLORS.indigo, COLORS.emerald, COLORS.amber, COLORS.red];

    return {
        textStyle: { fontFamily: FONT_SANS },
        tooltip: tooltipDefaults({
            trigger: 'axis',
            axisPointer: { type: 'none' },
            formatter(params) {
                const p = params?.[0];
                if (!p) {
                    return '';
                }
                const item = items[p.dataIndex];
                const cat = item?.category ? ` (${item.category})` : '';
                return `<div style="font-weight:700;">${item?.label ?? p.axisValue}${cat}</div>
                    <div style="margin-top:3px;font-family:${FONT_MONO};color:#93c5fd;">
                        ${p.value} Permintaan Test Drive
                    </div>`;
            },
        }),
        grid: { top: 8, left: 6, right: 34, bottom: 6, containLabel: true },
        xAxis: {
            type: 'value',
            min: 0,
            minInterval: 1,
            splitLine: { lineStyle: { color: COLORS.gridLine, type: 'dashed' } },
            axisLabel: { color: COLORS.mutedAxis, fontSize: 10, fontFamily: FONT_MONO },
        },
        yAxis: {
            type: 'category',
            data: labels,
            axisLine: { show: false },
            axisTick: { show: false },
            axisLabel: {
                color: COLORS.ink,
                fontSize: 11,
                fontWeight: 600,
                width: 118,
                overflow: 'truncate',
            },
        },
        series: [
            {
                name: 'Permintaan',
                type: 'bar',
                barWidth: 4,
                silent: true,
                data: values.map((v, idx) => ({
                    value: v,
                    itemStyle: {
                        color: palette[idx % palette.length],
                        borderRadius: 4,
                    },
                })),
            },
            {
                name: 'Jumlah Lead',
                type: 'scatter',
                symbolSize: 13,
                data: values.map((v, idx) => ({
                    value: v,
                    itemStyle: {
                        color: palette[idx % palette.length],
                        borderColor: '#ffffff',
                        borderWidth: 2,
                    },
                })),
                label: {
                    show: true,
                    position: 'right',
                    distance: 6,
                    fontFamily: FONT_MONO,
                    fontSize: 11,
                    fontWeight: 700,
                    color: COLORS.ink,
                },
            },
        ],
    };
}

function buildMekanikThroughputOption(cfg) {
    const labels = cfg.labels || [];
    const totalJobs = (cfg.totalJobs || []).map(Number);
    const completedJobs = (cfg.completedJobs || []).map(Number);

    return {
        textStyle: { fontFamily: FONT_SANS },
        tooltip: tooltipDefaults({
            trigger: 'axis',
            formatter(params) {
                if (!params?.length) {
                    return '';
                }
                const rows = params
                    .map((p) => `<div style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-top:4px;">
                        <span style="display:inline-flex;align-items:center;gap:6px;color:#cbd5e1;">
                            <span style="width:8px;height:8px;border-radius:99px;background:${p.borderColor || p.color};display:inline-block;"></span>
                            ${p.seriesName}
                        </span>
                        <span style="font-family:${FONT_MONO};font-weight:700;color:#ffffff;">${p.value} unit</span>
                    </div>`)
                    .join('');
                return `<div style="font-weight:700;margin-bottom:4px;">${params[0].axisValue}</div>${rows}`;
            },
        }),
        legend: {
            top: 0,
            left: 0,
            icon: 'circle',
            itemWidth: 9,
            itemHeight: 9,
            itemGap: 16,
            textStyle: { color: COLORS.slateText, fontSize: 12, fontWeight: 600 },
        },
        grid: { top: 42, left: 6, right: 12, bottom: 6, containLabel: true },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: labels,
            axisLine: { lineStyle: { color: COLORS.axisBorder } },
            axisTick: { show: false },
            axisLabel: { color: COLORS.mutedAxis, fontSize: 11, fontWeight: 600, margin: 10 },
        },
        yAxis: {
            type: 'value',
            min: 0,
            minInterval: 1,
            splitLine: { lineStyle: { color: COLORS.gridLine, type: 'dashed' } },
            axisLabel: { color: COLORS.mutedAxis, fontSize: 11, fontFamily: FONT_MONO },
        },
        series: [
            {
                name: 'Total Unit',
                type: 'line',
                smooth: 0.38,
                symbol: 'circle',
                symbolSize: 7,
                data: totalJobs,
                lineStyle: { width: 3, color: COLORS.blue },
                itemStyle: { color: '#ffffff', borderColor: COLORS.blue, borderWidth: 2 },
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(37, 99, 235, 0.18)' },
                        { offset: 1, color: 'rgba(37, 99, 235, 0.01)' },
                    ]),
                },
            },
            {
                name: 'Selesai',
                type: 'line',
                smooth: 0.38,
                symbol: 'circle',
                symbolSize: 7,
                data: completedJobs,
                lineStyle: { width: 3, color: COLORS.emerald },
                itemStyle: { color: '#ffffff', borderColor: COLORS.emerald, borderWidth: 2 },
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(5, 150, 105, 0.18)' },
                        { offset: 1, color: 'rgba(5, 150, 105, 0.01)' },
                    ]),
                },
            },
        ],
    };
}

function buildMekanikGaugeOption(cfg) {
    const value = Math.max(0, Math.min(100, Number(cfg.value) || 0));
    const accent = cfg.color || COLORS.emerald;

    return {
        textStyle: { fontFamily: FONT_SANS },
        series: [
            {
                type: 'gauge',
                startAngle: 205,
                endAngle: -25,
                min: 0,
                max: 100,
                radius: '92%',
                center: ['50%', '60%'],
                pointer: { show: false },
                progress: {
                    show: true,
                    roundCap: true,
                    width: 10,
                    itemStyle: { color: accent },
                },
                axisLine: {
                    roundCap: true,
                    lineStyle: {
                        width: 10,
                        color: [[1, '#e2e8f0']],
                    },
                },
                axisTick: { show: false },
                splitLine: { show: false },
                axisLabel: { show: false },
                title: { show: false },
                detail: {
                    valueAnimation: true,
                    offsetCenter: [0, '4%'],
                    fontSize: 20,
                    fontWeight: 800,
                    fontFamily: FONT_MONO,
                    color: COLORS.ink,
                    formatter: '{value}%',
                },
                data: [{ value }],
            },
        ],
    };
}

function buildOption(cfg, mode) {
    switch (cfg.type) {
        case 'owner-combo':
            return buildOwnerComboOption(cfg);
        case 'owner-ring':
            return buildOwnerRingOption(cfg);
        case 'superadmin-area':
            return buildSuperAdminAreaOption(cfg);
        case 'superadmin-heatmap':
            return buildSuperAdminHeatmapOption(cfg);
        case 'sales-funnel':
            return buildSalesFunnelOption(cfg, mode);
        case 'sales-lollipop':
            return buildSalesLollipopOption(cfg);
        case 'mekanik-throughput':
            return buildMekanikThroughputOption(cfg);
        case 'mekanik-gauge':
            return buildMekanikGaugeOption(cfg);
        default:
            return null;
    }
}

function initDashboardCharts() {
    const containers = document.querySelectorAll('[data-echarts-config]');
    containers.forEach((el) => {
        if (el.dataset.chartInitialized === 'true') {
            return;
        }

        let cfg;
        try {
            cfg = JSON.parse(el.dataset.echartsConfig || '{}');
        } catch {
            return;
        }

        const initialMode = el.dataset.echartsMode || 'funnel';
        const option = buildOption(cfg, initialMode);
        if (!option) {
            return;
        }

        const instance = echarts.init(el, undefined, { renderer: 'canvas' });
        instance.setOption(option);
        el.dataset.chartInitialized = 'true';
        el._echartsInstance = instance;

        if (typeof ResizeObserver !== 'undefined') {
            const ro = new ResizeObserver(() => {
                if (el.clientWidth > 0 && el.clientHeight > 0) {
                    instance.resize();
                }
            });
            ro.observe(el);
        }
    });

    // Switcher button wiring for Sales Funnel <-> Monthly Trend
    document.querySelectorAll('[data-echarts-toggle]').forEach((btn) => {
        if (btn.dataset.toggleWired === 'true') {
            return;
        }
        btn.dataset.toggleWired = 'true';

        btn.addEventListener('click', () => {
            const targetSelector = btn.dataset.echartsTarget;
            const nextMode = btn.dataset.echartsToggle;
            const targetEl = targetSelector ? document.querySelector(targetSelector) : null;
            if (!targetEl) {
                return;
            }

            let cfg;
            try {
                cfg = JSON.parse(targetEl.dataset.echartsConfig || '{}');
            } catch {
                return;
            }

            const option = buildOption(cfg, nextMode);
            const instance = echarts.getInstanceByDom(targetEl);
            if (instance && option) {
                targetEl.dataset.echartsMode = nextMode;
                instance.setOption(option, true);
            }

            const group = btn.closest('[data-echarts-toggle-group]');
            if (group) {
                group.querySelectorAll('[data-echarts-toggle]').forEach((sibling) => {
                    const isActive = sibling === btn;
                    sibling.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    sibling.classList.toggle('bg-white', isActive);
                    sibling.classList.toggle('text-slate-900', isActive);
                    sibling.classList.toggle('font-bold', isActive);
                    sibling.classList.toggle('shadow-sm', isActive);
                    sibling.classList.toggle('text-slate-600', !isActive);
                    sibling.classList.toggle('font-semibold', !isActive);
                });
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardCharts);
} else {
    initDashboardCharts();
}

window.addEventListener('resize', () => {
    document.querySelectorAll('[data-echarts-config]').forEach((el) => {
        const instance = echarts.getInstanceByDom(el);
        if (instance && el.clientWidth > 0 && el.clientHeight > 0) {
            instance.resize();
        }
    });
});
