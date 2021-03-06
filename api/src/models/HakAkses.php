<?php

namespace Model;

use Service\Db;

class HakAkses
{
    /**
     * simpan class Landa DB ke variabel #db.
     */
    private $db;

    /**
     * variabel untuk menyimpan nama tabel.
     */
    private $table;

    /**
     * konstruktor memanggil library landa Db.
     */
    public function __construct()
    {
        $this->db = Db::db();
        $this->table = 'm_roles';
    }

    /**
     * Ambil semua data perusahaan.
     *
     * @param array  $params
     * @param int    $limit
     * @param int    $offset
     * @param string $order
     */
    public function getAll($params = [], $limit = 0, $offset = 0, $order = '')
    {
        $this->db->select('*')
            ->from($this->table)
            ->where($this->table.'.is_deleted', '=', 0)
                 ;
        // Filter
        if (isset($params) && !is_array($params)) {
            // jika parameter dalam bentuk json
            $filter = (isset($params)) ? (array) json_decode($params) : [];
        } elseif (isset($params) && is_array($params)) {
            $filter = $params;
        }
        if (isset($params) && !empty($params)) {
            foreach ($filter as $key => $val) {
                if (!empty($val)) {
                    if ('id' == $key) {
                        $this->db->where('id', '=', $val);
                    }
                    if ('nama' == $key) {
                        $this->db->where('nama', 'like', $val);
                    }
                    // $this->db->where($key, 'like', $val);
                }
            }
        }
        // Set limit
        if (isset($limit) && !empty($limit)) {
            $this->db->limit($limit);
        }
        // Set offset
        if (isset($offset) && !empty($offset)) {
            $this->db->offset($offset);
        }
        $models = $this->db->findAll();
        $totalItem = $this->db->count();
        foreach ($models as $val) {
            // $val->akses = str_replace('true', 'false', $val->akses);
            $val->akses = json_decode($val->akses);
            $val->akses_perusahaan = json_decode($val->akses_perusahaan);
        }

        return [
            'data' => $models,
            'totalItem' => $totalItem,
        ];
    }

    /**
     * Method untuk menyimpan data perusahaan.
     *
     * @param array $data
     * @param array $customParams
     * @param mixed $params
     */
    public function save($params)
    {
        try {
            $params['m_perusahaan_id'] = isset($_SESSION['user']['m_perusahaan']['id']) && !empty($_SESSION['user']['m_perusahaan']['id']) ? (int) $_SESSION['user']['m_perusahaan']['id'] : null;
            if (isset($params['id']) && !empty($params['id'])) {
                $model = $this->db->update($this->table, $params, ['id' => $params['id']]);
            } else {
                $model = $this->db->insert($this->table, $params);
            }

            return [
                'status' => true,
                'data' => $model,
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Hapus hak akses.
     *
     * @param array $params
     *
     * @return array
     */
    public function delete($params)
    {
        try {
            $model = $this->db->update($this->table, ['is_deleted' => 1], ['id' => $params['id']]);

            return [
                'status' => true,
                'data' => $model,
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validasi data yang dikirim.
     *
     * @param array $data
     * @param array $custom
     */
    public function validasi($data, $custom = [])
    {
        $validasi = [
            'nama' => 'required',
        ];

        return validate($data, $validasi, $custom);
    }
}
