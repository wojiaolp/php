<?php
namespace Protocols;
class JsonNL
{
    /**
     * ������������
     * ����ܹ��õ��������򷵻ذ�����buffer�еĳ��ȣ����򷵻�0�����ȴ�����
     * ���Э�������⣬����Է���false����ǰ�ͻ������ӻ���˶Ͽ�
     * @param string $buffer
     * @return int
     */
    public static function input($buffer)
    {
        // ��û����ַ�"\n"λ��
        $pos = strpos($buffer, "\n");
        // û�л��з����޷���֪����������0�����ȴ�����
        if($pos === false)
        {
            return 0;
        }
        // �л��з������ص�ǰ�������������з���
        return $pos+1;
    }

    /**
     * ���������ͻ��˷������ݵ�ʱ����Զ�����
     * @param string $buffer
     * @return string
     */
    public static function encode($buffer)
    {
        // json���л��������ϻ��з���Ϊ��������ı��
        return json_encode($buffer)."\n";
    }

    /**
     * ����������յ��������ֽ�������input���ص�ֵ������0��ֵ���Զ�����
     * �����ݸ�onMessage�ص�������$data����
     * @param string $buffer
     * @return string
     */
    public static function decode($buffer)
    {
        // ȥ�����У���ԭ������
        return json_decode(trim($buffer), true);
    }
}
?>
