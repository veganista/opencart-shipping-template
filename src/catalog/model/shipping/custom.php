<?php
class ModelShippingCustom extends Model {
    public function getQuote($address) {
        $this->load->language('shipping/custom');

        $quote_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name");

        foreach ($query->rows as $result) {
            if ($this->config->get('custom_' . $result['geo_zone_id'] . '_status')) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$result['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

                if ($query->num_rows) {
                    $status = true;
                } else {
                    $status = false;
                }
            } else {
                $status = false;
            }

            if ($status) {
                $cost = '';
                $custom = $this->cart->getWeight();

                $rates = explode(',', $this->config->get('custom_' . $result['geo_zone_id'] . '_rate'));

                foreach ($rates as $rate) {
                    $data = explode(':', $rate);

                    if ($data[0] >= $custom) {
                        if (isset($data[1])) {
                            $cost = $data[1];
                        }

                        break;
                    }
                }

                $title = $this->config->get('custom_' . $result['geo_zone_id'] . '_name') ? $name = $this->config->get('custom_' . $result['geo_zone_id'] . '_name') : $result['name'];

                // Add the weight if needed
                // $title .= '  (' . $this->language->get('text_weight') . ' ' . $this->weight->format($custom, $this->config->get('config_custom_class_id')) . ')';

                if ((string)$cost != '') {
                    $quote_data['custom_' . $result['geo_zone_id']] = array(
                        'code'         => 'custom.custom_' . $result['geo_zone_id'],
                        'title'        => $title,
                        'cost'         => $cost,
                        'tax_class_id' => $this->config->get('custom_tax_class_id'),
                        'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('custom_tax_class_id'), $this->config->get('config_tax')))
                    );
                }
            }
        }

        $method_data = array();

        if ($quote_data) {
            $method_data = array(
                'code'       => 'custom',
                'title'      => $this->language->get('text_title'),
                'quote'      => $quote_data,
                'sort_order' => $this->config->get('custom_sort_order'),
                'error'      => false
            );
        }

        return $method_data;
    }
}
?>