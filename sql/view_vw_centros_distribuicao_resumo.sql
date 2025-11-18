-- View para exibir informações dos Centros de Distribuição

CREATE OR REPLACE VIEW public.vw_centros_distribuicao_resumo AS
SELECT 
    cd.id,
    cd.codigo,
    cd.nome,
    cd.endereco,
    cd.capacidade_maxima AS capacidade,
    COALESCE(SUM(ec.quantidade), 0) AS ocupacao,
    cd.status
FROM public.centros_distribuicao cd
LEFT JOIN public.estoque_cd ec ON ec.cd_id = cd.id
GROUP BY cd.id, cd.codigo, cd.nome, cd.endereco, cd.capacidade_maxima, cd.status;

-- Exemplo de consulta:
-- SELECT * FROM public.vw_centros_distribuicao_resumo;