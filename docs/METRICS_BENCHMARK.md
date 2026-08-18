# Metrics query-budget benchmark

Phase 6 workload metrics use two aggregate data queries after workspace resolution: one grouped task query and one member query. The automated Phase 6 acceptance test asserts a query budget of at most four queries, including framework/model overhead.

For production sizing, seed a representative workspace and measure p50/p95 dashboard latency with query logging disabled in the application process. External search is not introduced until database search latency or result quality is measured as insufficient.
