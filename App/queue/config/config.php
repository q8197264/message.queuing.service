<?php
/**
 * Passing in this constant as a flag will forcefully disable all other flags.
 * Use this if you want to temporarily disable the amqp.auto_ack ini setting.
 * 传递这个参数作为标志将完全禁用其他标志,如果你想临时禁用amqp.auto_ack设置起效
 */
define('AMQP_NOPARAM', 0);

/**
 * Durable exchanges and queues will survive a broker restart, complete with all of their data.
 * 持久化交换机和队列,当代理重启动后依然存在,并包括它们中的完整数据
 */
define('AMQP_DURABLE', 2);

/**
 * Passive exchanges and queues will not be redeclared, but the broker will throw an error if the exchange or queue does not exist.
 * 被动模式的交换机和队列不能被重新定义,但是如果交换机和队列不存在,代理将扔出一个错误提示
 */
define('AMQP_PASSIVE', 4);

/**
 * Valid for queues only, this flag indicates that only one client can be listening to and consuming from this queue.
 * 仅对队列有效,这个人标志定义队列仅允许一个客户端连接并且从其消费消息
 */
define('AMQP_EXCLUSIVE', 8);

/**
 * For exchanges, the auto delete flag indicates that the exchange will be deleted as soon as no more queues are bound
 * to it. If no queues were ever bound the exchange, the exchange will never be deleted. For queues, the auto delete
 * flag indicates that the queue will be deleted as soon as there are no more listeners subscribed to it. If no
 * subscription has ever been active, the queue will never be deleted. Note: Exclusive queues will always be
 * automatically deleted with the client disconnects.
 * 对交换机而言,自动删除标志表示交换机将在没有队列绑定的情况下被自动删除,如果从没有队列和其绑定过,这个交换机将不会被删除.
 * 对队列而言,自动删除标志表示如果没有消费者和你绑定的话将被自动删除,如果从没有消费者和其绑定,将不被删除,独占队列在客户断
 * 开连接的时候将总是会被删除
 */
define('AMQP_AUTODELETE', 16);

/**
 * Clients are not allowed to make specific queue bindings to exchanges defined with this flag.
 * 这个标志标识不允许自定义队列绑定到交换机上
 */
define('AMQP_INTERNAL', 32);

/**
 * When passed to the consume method for a clustered environment, do not consume from the local node.
 * 在集群环境消费方法中传递这个参数,表示将不会从本地站点消费消息
 */
define('AMQP_NOLOCAL', 64);

/**
 * When passed to the {@link AMQPQueue::get()} and {@link AMQPQueue::get()} methods as a flag,
 * the messages will be immediately marked as acknowledged by the server upon delivery.
 * 当在队列get方法中作为标志传递这个参数的时候,消息将在被服务器输出之前标志为acknowledged (已收到)
 */
define('AMQP_AUTOACK', 128);

/**
 * Passed on queue creation, this flag indicates that the queue should be deleted if it becomes empty.
 * 在队列建立时候传递这个参数,这个标志表示队列将在为空的时候被删除
 */
define('AMQP_IFEMPTY', 256);

/**
 * Passed on queue or exchange creation, this flag indicates that the queue or exchange should be
 * deleted when no clients are connected to the given queue or exchange.
 * 在交换机或者队列建立的时候传递这个参数,这个标志表示没有客户端连接的时候,交换机或者队列将被删除
 */
define('AMQP_IFUNUSED', 512);

/**
 * When publishing a message, the message must be routed to a valid queue. If it is not, an error will be returned.
 * 当发布消息的时候,消息必须被正确路由到一个有效的队列,否则将返回一个错误
 */
define('AMQP_MANDATORY', 1024);

/**
 * When publishing a message, mark this message for immediate processing by the broker. (High priority message.)
 * 当发布消息时候,这个消息将被立即处理.
 */
define('AMQP_IMMEDIATE', 2048);

/**
 * If set during a call to {@link AMQPQueue::ack()}, the delivery tag is treated as "up to and including", so that multiple
 * messages can be acknowledged with a single method. If set to zero, the delivery tag refers to a single message.
 * If the AMQP_MULTIPLE flag is set, and the delivery tag is zero, this indicates acknowledgement of all outstanding
 * messages.
 * 当在调用AMQPQueue::ack时候设置这个标志,传递标签将被视为最大包含数量,以便通过单个方法标示多个消息为已收到,如果设置为0
 * 传递标签指向单个消息,如果设置了AMQP_MULTIPLE,并且传递标签是0,将所有未完成消息标示为已收到
 */
define('AMQP_MULTIPLE', 4096);

/**
 * If set during a call to {@link AMQPExchange::bind()}, the server will not respond to the method.The client should not wait
 * for a reply method. If the server could not complete the method it will raise a channel or connection exception.
 * 当在调用AMQPExchange::bind()方法的时候,服务器将不响应请求,客户端将不应该等待响应,如果服务器无法完成该方法,将会抛出一个异常
 */
define('AMQP_NOWAIT', 8192);

/**
 * If set during a call to {@link AMQPQueue::nack()}, the message will be placed back to the queue.
 * 如果在调用AMQPQueue::nack方法时候设置,消息将会被传递回队列
 */
define('AMQP_REQUEUE', 16384);

/**
 * A direct exchange type.
 * direct类型交换机
 */
define('AMQP_EX_TYPE_DIRECT', 'direct');

/**
 * A fanout exchange type.
 * fanout类型交换机
 */
define('AMQP_EX_TYPE_FANOUT', 'fanout');

/**
 * A topic exchange type.
 * topic类型交换机
 */
define('AMQP_EX_TYPE_TOPIC', 'topic');

/**
 * A header exchange type.
 * header类型交换机
 */
define('AMQP_EX_TYPE_HEADERS', 'headers');

/**
 * socket连接超时设置
 */
define('AMQP_OS_SOCKET_TIMEOUT_ERRNO', 536870947);