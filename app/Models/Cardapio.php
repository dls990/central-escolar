<?php
// Gerencia os cardapios escolares.





namespace App\Models;

use Core\Model;

class Cardapio extends Model {
    // Lista todos os cardapios em ordem de dia da semana.
    public static function all(): array {
        $stmt = self::getConnection()->query("SELECT * FROM cardapios");
        $cardapios = $stmt->fetchAll();
        $ordemDias = [
            'Segunda-feira' => 1,
            'Terça-feira' => 2,
            'Quarta-feira' => 3,
            'Quinta-feira' => 4,
            'Sexta-feira' => 5
        ];
        usort($cardapios, static fn($a, $b) => ($ordemDias[$a['dia_semana']] ?? 99) <=> ($ordemDias[$b['dia_semana']] ?? 99));
        return $cardapios;
    }

    // Busca um cardapio pelo seu identificador.
    public static function find(int $id): ?array {
        $stmt = self::getConnection()->prepare("SELECT * FROM cardapios WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // Localiza o cardapio associado ao dia da semana informado.
    public static function getDoDia(string $diaSemana): ?array {
        $stmt = self::getConnection()->prepare("SELECT * FROM cardapios WHERE dia_semana = ? LIMIT 1");
        $stmt->execute([$diaSemana]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // Determina o dia atual e devolve o cardapio correspondente.
    public static function getCardapioDeHoje(): array {
        $diasMap = [
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Segunda-feira',
            0 => 'Segunda-feira'
        ];
        $diaAtual = (int)date('w');
        $nomeDia = $diasMap[$diaAtual] ?? 'Segunda-feira';

        $cardapio = self::getDoDia($nomeDia);
        if (!$cardapio) {
            $stmt = self::getConnection()->query("SELECT * FROM cardapios ORDER BY id ASC LIMIT 1");
            $cardapio = $stmt->fetch();
        }
        return $cardapio ?: [
            'id' => 1,
            'dia_semana' => 'Hoje',
            'prato_principal' => 'Prato Nutritivo Especial',
            'acompanhamento' => 'Arroz, Feijão e Salada Colorida',
            'sobremesa' => 'Fruta da Estação',
            'bebida' => 'Suco Natural',
            'calorias' => 450,
            'tags' => 'Vitamina C,Proteína',
            'emoji' => '🍽️'
        ];
    }

    // Insere um novo cardapio ou atualiza o registro existente.
    public static function salvar(array $dados, ?int $id = null): bool {
        $db = self::getConnection();
        $stmt = $db->prepare('SELECT id FROM cardapios WHERE dia_semana = ? AND id <> ? LIMIT 1');
        $stmt->execute([$dados['dia_semana'], $id ?? 0]);
        if ($stmt->fetchColumn()) {
            return false;
        }

        if ($id !== null) {
            $stmt = $db->prepare('SELECT id FROM cardapios WHERE id = ?');
            $stmt->execute([$id]);
            if (!$stmt->fetchColumn()) {
                return false;
            }

            $stmt = $db->prepare('UPDATE cardapios SET dia_semana = ?, prato_principal = ?, acompanhamento = ?, sobremesa = ?, bebida = ?, calorias = ?, tags = ?, emoji = ? WHERE id = ?');
            return $stmt->execute([
                $dados['dia_semana'],
                $dados['prato_principal'],
                $dados['acompanhamento'],
                $dados['sobremesa'],
                $dados['bebida'],
                $dados['calorias'],
                $dados['tags'],
                $dados['emoji'],
                $id
            ]);
        }

        $stmt = $db->prepare('INSERT INTO cardapios (dia_semana, prato_principal, acompanhamento, sobremesa, bebida, calorias, tags, emoji) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        return $stmt->execute([
            $dados['dia_semana'],
            $dados['prato_principal'],
            $dados['acompanhamento'],
            $dados['sobremesa'],
            $dados['bebida'],
            $dados['calorias'],
            $dados['tags'],
            $dados['emoji']
        ]);
    }

    // Copia os itens de um dia para outro sem sobrescrever o original.
    public static function copiar(int $origemId, string $diaDestino): bool {
        $origem = self::find($origemId);
        if (!$origem) {
            return false;
        }

        $destino = self::getDoDia($diaDestino);
        if ($destino && (int)$destino['id'] === $origemId) {
            return false;
        }

        unset($origem['id']);
        $origem['dia_semana'] = $diaDestino;
        return self::salvar($origem, $destino ? (int)$destino['id'] : null);
    }
}
